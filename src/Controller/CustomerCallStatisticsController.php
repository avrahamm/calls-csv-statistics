<?php

namespace App\Controller;

use App\Repository\CustomerCallStatisticRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/customer-call-statistics')]
class CustomerCallStatisticsController extends AbstractController
{
    private CustomerCallStatisticRepository $customerCallStatisticRepository;

    public function __construct(CustomerCallStatisticRepository $customerCallStatisticRepository)
    {
        $this->customerCallStatisticRepository = $customerCallStatisticRepository;
    }

    #[Route('', name: 'api_customer_call_statistics_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $statistics = $this->customerCallStatisticRepository->findAll();

        $formattedStatistics = [];
        foreach ($statistics as $statistic) {
            $formattedStatistics[] = [
                'customerId' => $statistic->getCustomerId(),
                'callsWithinContinent' => $statistic->getNumCallsWithinSameContinent(),
                'durationWithinContinent' => $statistic->getTotalDurationWithinSameCont(),
                'totalCalls' => $statistic->getTotalNumCalls(),
                'totalDuration' => $statistic->getTotalCallsDuration(),
                'lastUpdated' => $statistic->getLastUpdated()->format('Y-m-d H:i:s')
            ];
        }

        return $this->json($formattedStatistics);
    }

    #[Route('/check-updates', name: 'api_customer_call_statistics_check_updates', methods: ['POST'])]
    public function checkUpdates(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['customerUpdates']) || !is_array($data['customerUpdates'])) {
            return $this->json(['error' => 'Invalid request format'], 400);
        }

        $customerUpdates = $data['customerUpdates'];
        $result = $this->customerCallStatisticRepository->findUpdatedStatistics($customerUpdates);

        $formattedStatistics = [];
        foreach ($result['updatedStatistics'] as $statistic) {
            $formattedStatistics[] = [
                'customerId' => $statistic->getCustomerId(),
                'callsWithinContinent' => $statistic->getNumCallsWithinSameContinent(),
                'durationWithinContinent' => $statistic->getTotalDurationWithinSameCont(),
                'totalCalls' => $statistic->getTotalNumCalls(),
                'totalDuration' => $statistic->getTotalCallsDuration(),
                'lastUpdated' => $statistic->getLastUpdated()->format('Y-m-d H:i:s')
            ];
        }

        return $this->json([
            'updatedStatistics' => $formattedStatistics,
            'allCustomerIds' => $result['allCustomerIds']
        ]);
    }
}
