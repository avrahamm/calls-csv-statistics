<?php

namespace App\MessageHandler;

use App\Message\EnrichSourceContinentMessage;
use App\Message\EnrichWithinSameContMessage;
use App\Repository\CallRepository;
use App\Repository\UploadedFileRepository;
use App\Service\CallsSourceContinentEnricher;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsMessageHandler]
class EnrichSourceContinentMessageHandler
{
    private CallsSourceContinentEnricher $callsSourceContinentEnricher;
    private CallRepository $callRepository;
    private MessageBusInterface $messageBus;
    private LoggerInterface $logger;
    private ParameterBagInterface $parameterBag;
    private UploadedFileRepository $uploadedFileRepository;

    public function __construct(
        CallsSourceContinentEnricher $callsSourceContinentEnricher,
        CallRepository $callRepository,
        MessageBusInterface $messageBus,
        LoggerInterface $logger,
        ParameterBagInterface $parameterBag,
        UploadedFileRepository $uploadedFileRepository
    ) {
        $this->callsSourceContinentEnricher = $callsSourceContinentEnricher;
        $this->callRepository = $callRepository;
        $this->messageBus = $messageBus;
        $this->logger = $logger;
        $this->parameterBag = $parameterBag;
        $this->uploadedFileRepository = $uploadedFileRepository;
    }

    public function __invoke(EnrichSourceContinentMessage $message)
    {
        $uploadedFileId = $message->getUploadedFileId();
        $start = $message->getStart();
        $end = $message->getEnd();
        $offset = $message->getOffset();

        $this->logger->info('Starting to enrich source_continent for calls', [
            'uploaded_file_id' => $uploadedFileId,
            'start' => $start,
            'end' => $end,
            'offset' => $offset
        ]);

        try {
            // Fetch unique IPs from the calls table based on the indexes
            $uniqueIps = $this->callRepository->findUniqueSourceIpsByUploadedFileId($uploadedFileId, $start, $offset);
            $ipsCount = count($uniqueIps);

            if ($ipsCount == 0) {
                $this->logger->info('No more IPs to process', [
                    'uploaded_file_id' => $uploadedFileId
                ]);

                // Update ips_enriched timestamp in the uploaded_files table
                $updated = $this->uploadedFileRepository->updateIpsEnriched($uploadedFileId);

                if ($updated) {
                    $this->logger->info('Updated ips_enriched timestamp', [
                        'uploaded_file_id' => $uploadedFileId
                    ]);

                    // Check if both enrichments are complete
                    if ($this->uploadedFileRepository->areBothEnrichmentsComplete($uploadedFileId)) {
                        // Dispatch a message to update within_same_cont
                        $this->messageBus->dispatch(new EnrichWithinSameContMessage($uploadedFileId));
                        $this->logger->info('Dispatched EnrichWithinSameContMessage', [
                            'uploaded_file_id' => $uploadedFileId
                        ]);
                    }
                } else {
                    $this->logger->warning('Failed to update ips_enriched timestamp', [
                        'uploaded_file_id' => $uploadedFileId
                    ]);
                }
                return;
            }

            $this->logger->info('Fetched unique IPs for processing', [
                'uploaded_file_id' => $uploadedFileId,
                'ips_count' => $ipsCount
            ]);

            $newStart = $end;
            $newOffset = $this->parameterBag->get('enrich_source_continent_offset');
            $newEnd = $newStart + $newOffset;
            // Dispatch a new message with updated indexes
            // before processing the current batch,
            // only if the current batch has less than the offset number of IPs.
            if ($ipsCount == $newOffset) {
                $this->messageBus->dispatch(new EnrichSourceContinentMessage($uploadedFileId, $newStart, $newEnd, $newOffset));
                $this->logger->info('Dispatched new message for continued processing', [
                    'uploaded_file_id' => $uploadedFileId,
                    'new_start' => $newStart,
                    'new_end' => $newEnd
                ]);
            }

            // Process these IPs
            $updatedCount = $this->callsSourceContinentEnricher->enrichSourceContinent($uploadedFileId, $uniqueIps);

            $this->logger->info('Successfully enriched source_continent for calls', [
                'uploaded_file_id' => $uploadedFileId,
                'updated_count' => $updatedCount
            ]);

            // Check if there are any remaining IPs to process
            if ($ipsCount < $newOffset) {
                $this->logger->info('No more IPs to process after this batch', [
                    'uploaded_file_id' => $uploadedFileId
                ]);

                // Update ips_enriched timestamp in the uploaded_files table
                $updated = $this->uploadedFileRepository->updateIpsEnriched($uploadedFileId);

                if ($updated) {
                    $this->logger->info('Updated ips_enriched timestamp', [
                        'uploaded_file_id' => $uploadedFileId
                    ]);

                    // Check if both enrichments are complete
                    if ($this->uploadedFileRepository->areBothEnrichmentsComplete($uploadedFileId)) {
                        // Dispatch a message to update within_same_cont
                        $this->messageBus->dispatch(new EnrichWithinSameContMessage($uploadedFileId));
                        $this->logger->info('Dispatched EnrichWithinSameContMessage', [
                            'uploaded_file_id' => $uploadedFileId
                        ]);
                    }
                } else {
                    $this->logger->warning('Failed to update ips_enriched timestamp', [
                        'uploaded_file_id' => $uploadedFileId
                    ]);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error enriching source_continent for calls', [
                'uploaded_file_id' => $uploadedFileId,
                'start' => $start,
                'end' => $end,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
