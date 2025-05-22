<?php

namespace App\MessageHandler;

use App\Message\EnrichDestContinentMessage;
use App\Repository\CallRepository;
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

    public function __construct(
        CallsDestContinentEnricher $callsDestContinentEnricher,
        CallRepository $callRepository,
        MessageBusInterface $messageBus,
        LoggerInterface $logger,
        ParameterBagInterface $parameterBag
    ) {
        $this->callsDestContinentEnricher = $callsDestContinentEnricher;
        $this->callRepository = $callRepository;
        $this->messageBus = $messageBus;
        $this->logger = $logger;
        $this->parameterBag = $parameterBag;
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

            if ($phonesCount > 0) {
                $this->logger->info('Fetched unique phones for processing', [
                    'uploaded_file_id' => $uploadedFileId,
                    'phones_count' => $phonesCount
                ]);

                // Dispatch a new message with updated indexes before processing current batch
                $newStart = $end;
                $newOffset = $this->parameterBag->get('enrich_dest_continent_offset');
                $newEnd = $newStart + $newOffset;

                $this->messageBus->dispatch(new EnrichDestContinentMessage($uploadedFileId, $newStart, $newEnd, $newOffset));

                $this->logger->info('Dispatched new message for continued processing', [
                    'uploaded_file_id' => $uploadedFileId,
                    'new_start' => $newStart,
                    'new_end' => $newEnd
                ]);

                // Process these phones
                $updatedCount = $this->callsDestContinentEnricher->enrichDestContinent($uploadedFileId, $uniquePhones);

                $this->logger->info('Successfully enriched dest_continent for calls', [
                    'uploaded_file_id' => $uploadedFileId,
                    'updated_count' => $updatedCount
                ]);

            } else {
                $this->logger->info('No more phones to process', [
                    'uploaded_file_id' => $uploadedFileId
                ]);
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
