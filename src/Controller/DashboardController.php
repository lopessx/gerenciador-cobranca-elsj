<?php

namespace App\Controller;

use App\Repository\BillingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('')]
#[IsGranted('ROLE_ADMIN or ROLE_OPERATOR or ROLE_READER')]
class DashboardController extends AbstractController
{
    public function __construct(
        private readonly BillingRepository $billingRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('', name: 'dashboard')]
    public function index(): Response
    {
        $user = $this->getUser();

        // Get billings - admin sees all, others see their company's
        if ($user->isAdmin()) {
            $billings = $this->billingRepository->findBy([], ['createdAt' => 'DESC'], 10);
            $totalActive = $this->billingRepository->count(['status' => 'pending']);
            $totalPaid = $this->billingRepository->count(['status' => 'paid']);
            $totalOverdue = $this->billingRepository->count(['status' => 'overdue']);
        } else {
            // Operator/Reader: only see billings of their companies
            $companies = $user->getCompanies();
            $billings = [];
            $totalActive = 0;
            $totalPaid = 0;
            $totalOverdue = 0;
            
            foreach ($companies as $company) {
                $companyBillings = $this->billingRepository->findBy(['company' => $company], ['createdAt' => 'DESC'], 10);
                $billings = array_merge($billings, $companyBillings);
                
                $totalActive += $this->billingRepository->count(['company' => $company, 'status' => 'pending']);
                $totalPaid += $this->billingRepository->count(['company' => $company, 'status' => 'paid']);
                $totalOverdue += $this->billingRepository->count(['company' => $company, 'status' => 'overdue']);
            }
            
            // Sort by createdAt DESC and limit to 10
            usort($billings, function($a, $b) {
                return $b->getCreatedAt() <=> $a->getCreatedAt();
            });
            $billings = array_slice($billings, 0, 10);
        }

        // Get recent billings (last 5)
        $recentBillings = array_slice($billings, 0, 5);

        // Calculate totals
        $amountPending = 0;
        $amountPaid = 0;
        foreach ($billings as $b) {
            if ($b->getStatus() === 'pending') {
                $amountPending += $b->getTotalAmount();
            } elseif ($b->getStatus() === 'paid') {
                $amountPaid += $b->getTotalAmount();
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'totalActive' => $totalActive,
            'totalPaid' => $totalPaid,
            'totalOverdue' => $totalOverdue,
            'amountPending' => $amountPending,
            'amountPaid' => $amountPaid,
            'recentBillings' => $recentBillings,
            'billings' => $billings,
        ]);
    }
}