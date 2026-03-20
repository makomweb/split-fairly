<?php

namespace App\Controller\API;

use App\Async\GenerateReportMessage;
use App\Entity\Report;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Uuid;

#[Route('/api', name: 'api.')]
final class ReportController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ReportRepository $reportRepository,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    #[Route('/report/calculation', name: 'report.calculation', methods: ['POST'])]
    public function initiate(): JsonResponse
    {
        $compensationId = (new \DateTimeImmutable())->format('Y-m-d');
        $checksum = hash('sha256', $compensationId);

        $existingReport = $this->reportRepository->findByCompensationIdAndChecksum(
            $compensationId,
            $checksum
        );

        if ($existingReport) {
            return new JsonResponse([
                'id' => $existingReport->getUuid()->toRfc4122(),
                'status' => $existingReport->getStatus(),
                'filePath' => $existingReport->getFilePath(),
            ]);
        }

        $report = new Report($compensationId, $checksum);
        $this->entityManager->persist($report);
        $this->entityManager->flush();

        $reportId = $report->getId();
        if (null === $reportId) {
            throw new \RuntimeException('Report ID should not be null after flush');
        }

        $this->messageBus->dispatch(new GenerateReportMessage(
            $reportId,
            $compensationId
        ));

        return new JsonResponse([
            'id' => $report->getUuid()->toRfc4122(),
            'status' => $report->getStatus(),
        ], Response::HTTP_ACCEPTED);
    }

    #[Route('/report/{id}/status', name: 'report.status', methods: ['GET'])]
    public function getStatus(string $id): JsonResponse
    {
        try {
            $uuid = Uuid::fromString($id);
        } catch (\Exception) {
            return new JsonResponse(['error' => 'Invalid report ID'], Response::HTTP_BAD_REQUEST);
        }

        $report = $this->reportRepository->findOneBy(['uuid' => $uuid]);

        if (!$report) {
            return new JsonResponse(['error' => 'Report not found'], Response::HTTP_NOT_FOUND);
        }

        $response = [
            'id' => $report->getUuid()->toRfc4122(),
            'status' => $report->getStatus(),
            'createdAt' => $report->getCreatedAt()->format(\DateTimeInterface::ATOM),
        ];

        if ($report->isCompleted() && $report->getFilePath()) {
            $response['downloadUrl'] = sprintf('/api/report/%s/download', $report->getUuid()->toRfc4122());
        }

        if ($report->isFailed()) {
            $response['error'] = $report->getErrorMessage();
        }

        return new JsonResponse($response);
    }

    #[Route('/report/{id}/download', name: 'report.download', methods: ['GET'])]
    public function download(string $id): Response
    {
        try {
            $uuid = Uuid::fromString($id);
        } catch (\Exception) {
            return new Response('Invalid report ID', Response::HTTP_BAD_REQUEST);
        }

        $report = $this->reportRepository->findOneBy(['uuid' => $uuid]);

        if (!$report || !$report->isCompleted() || !$report->getFilePath()) {
            return new Response('Report not found or not ready', Response::HTTP_NOT_FOUND);
        }

        if (!file_exists($report->getFilePath())) {
            return new Response('Report file not found', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $pdfContent = file_get_contents($report->getFilePath());

        if (false === $pdfContent) {
            return new Response('Failed to read report file', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(
            $pdfContent,
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="split-fairly-calculation.pdf"',
            ]
        );
    }
}
