<?php

namespace App\Service;

use App\Repository\CallRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CallsSourceContinentEnricher
{
    private CallRepository $callRepository;
    private LoggerInterface $logger;
    private HttpClientInterface $httpClient;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        CallRepository $callRepository,
        LoggerInterface $logger,
        HttpClientInterface $httpClient,
        ParameterBagInterface $parameterBag
    ) {
        $this->callRepository = $callRepository;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->parameterBag = $parameterBag;
    }

    /**
     * Enrich source_continent for calls with empty source_continent values
     * 
     * @param int $uploadedFileId The ID of the uploaded file
     * @param array $uniqueIps Array of unique IP addresses to process
     * @return int Number of updated calls
     */
    public function enrichSourceContinent(int $uploadedFileId, array $uniqueIps): int
    {
        $this->logger->info('Enriching source_continent for calls', [
            'uploaded_file_id' => $uploadedFileId,
            'unique_ips_count' => count($uniqueIps)
        ]);

        $totalUpdated = 0;
        $apiKey = $this->parameterBag->get('ip_geolocation_api_key');

        // Process IPs one by one for now (naive implementation)
        // In future steps, we will convert it to batch style
        $ipToContinent = [];
        
        foreach ($uniqueIps as $ip) {
            try {
                $continentCode = $this->getContinentCodeByIp($ip, $apiKey);
                
                if ($continentCode !== null) {
                    $ipToContinent[$ip] = $continentCode;
                }
            } catch (\Exception $e) {
                $this->logger->error('Error fetching continent code for IP', [
                    'ip' => $ip,
                    'error' => $e->getMessage()
                ]);
            }
        }

        if (!empty($ipToContinent)) {
            // Update source_continent for calls with matching source_ip in bulk
            $updated = $this->callRepository->updateSourceContinentInBulk($ipToContinent);
            $totalUpdated += $updated;

            $this->logger->debug('Updated source_continent for IPs', [
                'ips_count' => count($ipToContinent),
                'updated_calls' => $updated
            ]);
        }

        $this->logger->info('Completed enriching source_continent for calls', [
            'uploaded_file_id' => $uploadedFileId,
            'total_updated_calls' => $totalUpdated
        ]);

        return $totalUpdated;
    }

    /**
     * Get continent code for an IP address using ipgeolocation.io API
     * 
     * @param string $ip IP address
     * @param string $apiKey API key for ipgeolocation.io
     * @return string|null Continent code or null if not found
     */
    private function getContinentCodeByIp(string $ip, string $apiKey): ?string
    {
        $url = "https://api.ipgeolocation.io/ipgeo";
        
        $response = $this->httpClient->request('GET', $url, [
            'query' => [
                'apiKey' => $apiKey,
                'ip' => $ip,
                'fields' => 'continent_code'
            ]
        ]);

        if ($response->getStatusCode() === 200) {
            $data = $response->toArray();
            return $data['continent_code'] ?? null;
        }

        return null;
    }
}