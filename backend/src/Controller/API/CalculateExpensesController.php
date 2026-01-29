<?php

namespace App\Controller\API;

use App\SplitFairly\Calculator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api.')]
class CalculateExpensesController extends AbstractController
{
    public function __construct(
        private readonly Calculator $calculator,
    ) {
    }

    #[Route('/calculate', name: 'calculate', methods: ['GET'])]
    public function calculate(): JsonResponse
    {
        $result = $this->calculator->calculate();

        return $this->json($result);
    }
}
