<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\Patient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/patients')]
class PatientController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('', name: 'admin_patients')]
    public function index(): Response
    {
        $patients = $this->entityManager->getRepository(Patient::class)->findBy([], ['name' => 'ASC']);

        return $this->render('admin/patients/index.html.twig', [
            'patients' => $patients,
        ]);
    }

    #[Route('/new', name: 'admin_patients_new')]
    public function new(Request $request): Response
    {
        $companies = $this->entityManager->getRepository(Company::class)->findBy([], ['name' => 'ASC']);

        if ($request->isMethod('POST')) {
            $company = $this->entityManager->getRepository(Company::class)->find((int) $request->request->get('company_id'));
            $patient = new Patient();
            $patient->setCompany($company);
            $patient->setName((string) $request->request->get('name'));
            $patient->setEmail((string) $request->request->get('email'));
            $patient->setCpf((string) $request->request->get('cpf'));

            $this->entityManager->persist($patient);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_patients');
        }

        return $this->render('admin/patients/new.html.twig', [
            'companies' => $companies,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_patients_edit')]
    public function edit(Patient $patient, Request $request): Response
    {
        $companies = $this->entityManager->getRepository(Company::class)->findBy([], ['name' => 'ASC']);

        if ($request->isMethod('POST')) {
            $company = $this->entityManager->getRepository(Company::class)->find((int) $request->request->get('company_id'));
            $patient->setCompany($company);
            $patient->setName((string) $request->request->get('name'));
            $patient->setEmail((string) $request->request->get('email'));
            $patient->setCpf((string) $request->request->get('cpf'));

            $this->entityManager->flush();

            return $this->redirectToRoute('admin_patients');
        }

        return $this->render('admin/patients/edit.html.twig', [
            'patient' => $patient,
            'companies' => $companies,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_patients_delete')]
    public function delete(Patient $patient): Response
    {
        $this->entityManager->remove($patient);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_patients');
    }
}
