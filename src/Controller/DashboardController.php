<?php

namespace App\Controller;

use App\Entity\Billing;
use App\Entity\Company;
use App\Entity\Installment;
use App\Entity\Patient;
use App\Repository\BillingRepository;
use App\Service\BillingDistributor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    public function __construct(
        private readonly BillingDistributor $billingDistributor,
        private readonly EntityManagerInterface $entityManager,
        private readonly BillingRepository $billingRepository,
    ) {
    }

    #[Route('/', name: 'dashboard')]
    public function index(Request $request): Response
    {
        $installments = [];
        $summary = null;
        $message = null;

        if ($request->isMethod('POST')) {
            $totalAmount = (int) $request->request->get('total_amount', 0);
            $entryAmount = (int) $request->request->get('entry_amount', 0);
            $installmentsCount = max(1, (int) $request->request->get('installments_count', 1));
            $patientName = trim((string) $request->request->get('patient_name', ''));
            $patientCpf = trim((string) $request->request->get('patient_cpf', ''));
            $companyName = trim((string) $request->request->get('company_name', ''));

            $installments = $this->billingDistributor->distribute($totalAmount, $entryAmount, $installmentsCount);

            $summary = [
                'total_amount' => $totalAmount,
                'entry_amount' => $entryAmount,
                'installments_count' => $installmentsCount,
                'amount_to_parcel' => max(0, $totalAmount - $entryAmount),
                'installments' => $installments,
            ];

            if ($patientName !== '' && $companyName !== '') {
                $company = new Company();
                $company->setName($companyName);
                $company->setEmail(sprintf('%s@sistema.local', strtolower(str_replace(' ', '.', $companyName))));

                $patient = new Patient();
                $patient->setCompany($company);
                $patient->setName($patientName);
                $patient->setCpf($patientCpf !== '' ? $patientCpf : '00000000000');

                $billing = new Billing();
                $billing->setCompany($company);
                $billing->setPatient($patient);
                $billing->setTotalAmount($totalAmount);
                $billing->setEntryAmount($entryAmount);

                foreach ($installments as $number => $amount) {
                    $installment = new Installment();
                    $installment->setInstallmentNumber($number);
                    $installment->setAmount($amount);
                    $installment->setDueDate(new \DateTimeImmutable(sprintf('+%d months', $number - 1)));
                    $billing->addInstallment($installment);
                }

                $this->entityManager->persist($company);
                $this->entityManager->persist($patient);
                $this->entityManager->persist($billing);
                $this->entityManager->flush();

                $message = 'Cobrança cadastrada com sucesso.';
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'installments' => $installments,
            'summary' => $summary,
            'message' => $message,
            'billings' => $this->billingRepository->findBy([], ['createdAt' => 'DESC'], 5),
        ]);
    }
}
