<?php

namespace App\Service;

use App\Repository\CallRepository;
use App\Repository\IpGeolocationCacheRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CallsSourceContinentEnricher
{
    private CallRepository $callRepository;
    private LoggerInterface $logger;
    private HttpClientInterface $httpClient;
    private ParameterBagInterface $parameterBag;
    private IpGeolocationCacheRepository $ipGeolocationCacheRepository;

    public function __construct(
        CallRepository $callRepository,
        LoggerInterface $logger,
        HttpClientInterface $httpClient,
        ParameterBagInterface $parameterBag,
        IpGeolocationCacheRepository $ipGeolocationCacheRepository
    ) {
        $this->callRepository = $callRepository;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->parameterBag = $parameterBag;
        $this->ipGeolocationCacheRepository = $ipGeolocationCacheRepository;
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
        $ipToContinent = [];

        // Step 1: Check if IPs exist in the ip_geolocation_cache table
        $cachedIpData = $this->ipGeolocationCacheRepository->findContinentCodesForIps($uniqueIps);

        // Add cached IPs to the result dictionary
        $ipToContinent = $cachedIpData;

        // Step 2: Find IPs not in the cache
        $ipsToFetch = array_diff($uniqueIps, array_keys($cachedIpData));

        if (!empty($ipsToFetch)) {
            $newIpData = [];

            // Process IPs not found in cache
            foreach ($ipsToFetch as $ip) {
                try {
                    $continentCode = $this->getContinentCodeByIp($ip, $apiKey);

                    if ($continentCode !== null) {
                        $ipToContinent[$ip] = $continentCode;
                        $newIpData[$ip] = $continentCode;
                    }
                } catch (\Exception $e) {
                    $this->logger->error('Error fetching continent code for IP', [
                        'ip' => $ip,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Step 3: Update the cache with new IP data
            if (!empty($newIpData)) {
                try {
                    $this->ipGeolocationCacheRepository->insertBulk($newIpData);
                } catch (\Exception $e) {
                    $this->logger->error('Error updating IP geolocation cache', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }

        // Step 4: Update source_continent for calls with matching source_ip in bulk
        if (!empty($ipToContinent)) {
            $updated = $this->callRepository->updateSourceContinentInBulk($ipToContinent);
            $totalUpdated += $updated;
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

        try {
            $response = $this->httpClient->request('GET', $url, [
                'query' => [
                    'apiKey' => $apiKey,
                    'ip' => $ip,
                    'fields' => 'continent_code'
                ]
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === 200) {
                $data = $response->toArray();
                $continentCode = $data['continent_code'] ?? null;

                return $continentCode;
            } else {
                $this->logger->warning('Received non-200 status code from ipgeolocation.io', [
                    'ip' => $ip,
                    'status_code' => $statusCode
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Exception while making API call to ipgeolocation.io', [
                'ip' => $ip,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return null;
    }
}
