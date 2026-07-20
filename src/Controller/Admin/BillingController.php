<?php

namespace App\Controller\Admin;

use App\Entity\Billing;
use App\Entity\Installment;
use App\Entity\Patient;
use App\Service\BillingDistributor;
use App\Service\BillingPdfService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/billings')]
class BillingController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BillingDistributor $billingDistributor,
        private readonly BillingPdfService $billingPdfService,
    ) {
    }

    #[Route('', name: 'admin_billings')]
    #[IsGranted(['ROLE_ADMIN', 'ROLE_OPERATOR'])]
    public function index(): Response
    {
        $user = $this->getUser();
        
        if ($user->isAdmin()) {
            $billings = $this->entityManager->getRepository(Billing::class)->findBy([], ['createdAt' => 'DESC']);
        } else {
            // Operator: only see billings of their companies
            $billings = [];
            foreach ($user->getCompanies() as $company) {
                $companyBillings = $this->entityManager->getRepository(Billing::class)->findBy(['company' => $company], ['createdAt' => 'DESC']);
                $billings = array_merge($billings, $companyBillings);
            }
        }

        return $this->render('admin/billings/index.html.twig', [
            'billings' => $billings,
        ]);
    }

    #[Route('/new', name: 'admin_billings_new')]
    #[IsGranted(['ROLE_ADMIN', 'ROLE_OPERATOR'])]
    public function new(Request $request): Response
    {
        $user = $this->getUser();
        $companies = $this->entityManager->getRepository(Company::class)->findBy([], ['name' => 'ASC']);

        // If operator, restrict to their companies
        if (!$user->isAdmin()) {
            $companies = $user->getCompanies();
        }

        $patients = $this->entityManager->getRepository(Patient::class)->findBy([], ['name' => 'ASC']);

        if ($request->isMethod('POST')) {
            $patientId = $request->request->get('patient_id');
            $totalAmount = (int) $request->request->get('total_amount', 0);
            $entryAmount = (int) $request->request->get('entry_amount', 0);
            $installmentsCount = max(1, (int) $request->request->get('installments_count', 1));

            $patient = $this->entityManager->getRepository(Patient::class)->find($patientId);

            if (null === $patient) {
                $this->addFlash('error', 'Paciente não encontrado.');
                return $this->redirectToRoute('admin_billings_new');
            }

            // Check if operator is allowed to create billing for this patient's company
            if (!$user->isAdmin() && !$user->getCompanies()->contains($patient->getCompany())) {
                $this->addFlash('error', 'Você não tem permissão para criar cobranças para este paciente.');
                return $this->redirectToRoute('admin_billings_new');
            }

            if ($entryAmount > $totalAmount) {
                $this->addFlash('error', 'A entrada não pode ser maior que o valor total.');
                return $this->redirectToRoute('admin_billings_new');
            }

            $billing = new Billing();
            $billing->setCompany($patient->getCompany());
            $billing->setPatient($patient);
            $billing->setCreatedBy($user);
            $billing->setTotalAmount($totalAmount);
            $billing->setEntryAmount($entryAmount);

            $installmentValues = $this->billingDistributor->distribute($totalAmount, $entryAmount, $installmentsCount);

            foreach ($installmentValues as $number => $amount) {
                $installment = new Installment();
                $installment->setInstallmentNumber($number);
                $installment->setAmount($amount);
                $installment->setDueDate(new \DateTimeImmutable(sprintf('+%d months', $number - 1)));
                $billing->addInstallment($installment);
            }

            $this->entityManager->persist($billing);
            $this->entityManager->flush();

            // Generate PDF after persistence
            $this->billingPdfService->generateBillingPdf($billing);
            $this->entityManager->flush();

            $this->addFlash('success', 'Cobrança cadastrada com sucesso.');
            return $this->redirectToRoute('admin_billings_show', ['id' => $billing->getId()]);
        }

        return $this->render('admin/billings/new.html.twig', [
            'patients' => $patients,
            'companies' => $companies,
        ]);
    }

    #[Route('/{id}', name: 'admin_billings_show')]
    #[IsGranted(['ROLE_ADMIN', 'ROLE_OPERATOR'])]
    public function show(Billing $billing): Response
    {
        $user = $this->getUser();
        
        // Check if operator has access to this billing's company
        if (!$user->isAdmin() && !$user->getCompanies()->contains($billing->getCompany())) {
            $this->addFlash('error', 'Você não tem permissão para visualizar esta cobrança.');
            return $this->redirectToRoute('admin_billings');
        }

        return $this->render('admin/billings/show.html.twig', [
            'billing' => $billing,
        ]);
    }

    #[Route('/{id}/pdf', name: 'admin_billings_pdf')]
    #[IsGranted(['ROLE_ADMIN', 'ROLE_OPERATOR'])]
    public function pdf(Billing $billing): Response
    {
        $user = $this->getUser();
        
        // Check if operator has access to this billing's company
        if (!$user->isAdmin() && !$user->getCompanies()->contains($billing->getCompany())) {
            $this->addFlash('error', 'Você não tem permissão para este PDF.');
            return $this->redirectToRoute('admin_billings');
        }

        $pdfPath = $billing->getPdfPath();
        if (!$pdfPath || !file_exists($pdfPath)) {
            $this->billingPdfService->generateBillingPdf($billing);
            $this->entityManager->flush();
            $pdfPath = $billing->getPdfPath();
        }

        if (!$pdfPath || !file_exists($pdfPath)) {
            throw $this->createNotFoundException('PDF não encontrado.');
        }

        return $this->file($pdfPath, sprintf('cobranca-%s.pdf', $billing->getId()));
    }

    #[Route('/{id}/delete', name: 'admin_billings_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Billing $billing): Response
    {
        // Remove PDF if exists
        $pdfPath = $billing->getPdfPath();
        if ($pdfPath && file_exists($pdfPath)) {
            unlink($pdfPath);
        }

        $this->entityManager->remove($billing);
        $this->entityManager->flush();

        $this->addFlash('success', 'Cobrança excluída com sucesso.');
        return $this->redirectToRoute('admin_billings');
    }
}