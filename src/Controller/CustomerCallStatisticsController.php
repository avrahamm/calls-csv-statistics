<?php

namespace App\Controller;

use App\Repository\CustomerCallStatisticRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
                'totalDuration' => $statistic->getTotalCallsDuration()
            ];
        }
        
        return $this->json($formattedStatistics);
    }
}