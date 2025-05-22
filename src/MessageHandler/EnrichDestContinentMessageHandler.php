<?php

namespace App\MessageHandler;

use App\Message\EnrichDestContinentMessage;
use App\Message\EnrichWithinSameContMessage;
use App\Repository\CallRepository;
use App\Repository\UploadedFileRepository;
use App\Service\CallsDestContinentEnricher;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsMessageHandler]
class EnrichDestContinentMessageHandler
{
    private CallsDestContinentEnricher $callsDestContinentEnricher;
    private CallRepository $callRepository;
    private MessageBusInterface $messageBus;
    private LoggerInterface $logger;
    private ParameterBagInterface $parameterBag;
    private UploadedFileRepository $uploadedFileRepository;

    public function __construct(
        CallsDestContinentEnricher $callsDestContinentEnricher,
        CallRepository $callRepository,
        MessageBusInterface $messageBus,
        LoggerInterface $logger,
        ParameterBagInterface $parameterBag,
        UploadedFileRepository $uploadedFileRepository
    ) {
        $this->callsDestContinentEnricher = $callsDestContinentEnricher;
        $this->callRepository = $callRepository;
        $this->messageBus = $messageBus;
        $this->logger = $logger;
        $this->parameterBag = $parameterBag;
        $this->uploadedFileRepository = $uploadedFileRepository;
    }

    public function __invoke(EnrichDestContinentMessage $message)
    {
        $uploadedFileId = $message->getUploadedFileId();
        $start = $message->getStart();
        $end = $message->getEnd();
        $offset = $message->getOffset();

        $this->logger->info('Starting to enrich dest_continent for calls', [
            'uploaded_file_id' => $uploadedFileId,
            'start' => $start,
            'end' => $end,
            'offset' => $offset
        ]);

        try {
            // Fetch unique phones from the calls table based on the indexes
            $uniquePhones = $this->callRepository->findUniquePhonesByUploadedFileId($uploadedFileId, $start, $offset);
            $phonesCount = count($uniquePhones);

            if ($phonesCount == 0) {
                $this->logger->info('No more phones to process', [
                    'uploaded_file_id' => $uploadedFileId
                ]);

                // Update phones_enriched timestamp in the uploaded_files table
                $updated = $this->uploadedFileRepository->updatePhonesEnriched($uploadedFileId);

                if ($updated) {
                    $this->logger->info('Updated phones_enriched timestamp', [
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
                    $this->logger->warning('Failed to update phones_enriched timestamp', [
                        'uploaded_file_id' => $uploadedFileId
                    ]);
                }
                return;
            }

            $this->logger->info('Fetched unique phones for processing', [
                'uploaded_file_id' => $uploadedFileId,
                'phones_count' => $phonesCount
            ]);

            $newStart = $end;
            $newOffset = $this->parameterBag->get('enrich_dest_continent_offset');
            $newEnd = $newStart + $newOffset;
            // Dispatch a new message with updated indexes
            // before processing the current batch,
            // only if the current batch has less than the offset number of phones.
            if ($phonesCount == $newOffset) {
                $this->messageBus->dispatch(new EnrichDestContinentMessage($uploadedFileId, $newStart, $newEnd, $newOffset));
                $this->logger->info('Dispatched new message for continued processing', [
                    'uploaded_file_id' => $uploadedFileId,
                    'new_start' => $newStart,
                    'new_end' => $newEnd
                ]);
            }

            // Process these phones
            $updatedCount = $this->callsDestContinentEnricher->enrichDestContinent($uploadedFileId, $uniquePhones);

            $this->logger->info('Successfully enriched dest_continent for calls', [
                'uploaded_file_id' => $uploadedFileId,
                'updated_count' => $updatedCount
            ]);

            // Check if there are any remaining phones to process
            if ($phonesCount < $newOffset) {
                $this->logger->info('No more phones to process after this batch', [
                    'uploaded_file_id' => $uploadedFileId
                ]);

                // Update phones_enriched timestamp in the uploaded_files table
                $updated = $this->uploadedFileRepository->updatePhonesEnriched($uploadedFileId);

                if ($updated) {
                    $this->logger->info('Updated phones_enriched timestamp', [
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
                    $this->logger->warning('Failed to update phones_enriched timestamp', [
                        'uploaded_file_id' => $uploadedFileId
                    ]);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error('Error enriching dest_continent for calls', [
                'uploaded_file_id' => $uploadedFileId,
                'start' => $start,
                'end' => $end,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
