<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\Patient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/patients')]
class PatientController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('', name: 'admin_patients')]
    #[IsGranted(['ROLE_ADMIN', 'ROLE_OPERATOR'])]
    public function index(): Response
    {
        $user = $this->getUser();
        if ($user->isAdmin()) {
            $patients = $this->entityManager->getRepository(Patient::class)->findBy([], ['name' => 'ASC']);
        } else {
            // Operator: only see patients of their companies
            $companies = $user->getCompanies();
            $patients = [];
            foreach ($companies as $company) {
                $patients = array_merge($patients, $company->getPatients()->toArray());
            }
            // Sort by name
            usort($patients, function($a, $b) {
                return strcmp($a->getName(), $b->getName());
            });
        }

        return $this->render('admin/patients/index.html.twig', [
            'patients' => $patients,
        ]);
    }

    #[Route('/new', name: 'admin_patients_new')]
    #[IsGranted(['ROLE_ADMIN', 'ROLE_OPERATOR'])]
    public function new(Request $request): Response
    {
        $user = $this->getUser();
        $companies = $this->entityManager->getRepository(Company::class)->findBy([], ['name' => 'ASC']);

        // If operator, restrict to their companies
        if (!$user->isAdmin()) {
            $companies = $user->getCompanies();
        }

        if ($request->isMethod('POST')) {
            $company = $this->entityManager->getRepository(Company::class)->find($request->request->get('company_id'));

            if (!$company) {
                $this->addFlash('error', 'Empresa não encontrada.');
                return $this->redirectToRoute('admin_patients_new');
            }

            // Check if operator is allowed to create patient for this company
            if (!$user->isAdmin() && !$user->getCompanies()->contains($company)) {
                $this->addFlash('error', 'Você não tem permissão para criar pacientes nesta empresa.');
                return $this->redirectToRoute('admin_patients_new');
            }

            $patient = new Patient();
            $patient->setCompany($company);
            $patient->setName((string) $request->request->get('name'));
            $patient->setEmail((string) $request->request->get('email') ?: null);
            $patient->setPhone((string) $request->request->get('phone') ?: null);
            $patient->setCpf((string) $request->request->get('cpf') ?: null);
            $patient->setNoEmail((bool) $request->request->get('no_email', false));

            $birthday = $request->request->get('birthday');
            if ($birthday) {
                $patient->setBirthday(new \DateTimeImmutable($birthday));
            }

            $this->entityManager->persist($patient);
            $this->entityManager->flush();

            $this->addFlash('success', 'Paciente cadastrado com sucesso.');

            return $this->redirectToRoute('admin_patients');
        }

        return $this->render('admin/patients/new.html.twig', [
            'companies' => $companies,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_patients_edit')]
    #[IsGranted(['ROLE_ADMIN', 'ROLE_OPERATOR'])]
    public function edit(Patient $patient, Request $request): Response
    {
        $user = $this->getUser();
        $companies = $this->entityManager->getRepository(Company::class)->findBy([], ['name' => 'ASC']);

        // If operator, restrict to their companies
        if (!$user->isAdmin()) {
            $companies = $user->getCompanies();
            // Check if the patient belongs to one of the operator's companies
            if (!$user->getCompanies()->contains($patient->getCompany())) {
                $this->addFlash('error', 'Você não tem permissão para editar este paciente.');
                return $this->redirectToRoute('admin_patients');
            }
        }

        if ($request->isMethod('POST')) {
            $company = $this->entityManager->getRepository(Company::class)->find($request->request->get('company_id'));

            if (!$company) {
                $this->addFlash('error', 'Empresa não encontrada.');
                return $this->redirectToRoute('admin_patients_edit', ['id' => $patient->getId()]);
            }

            // Check if operator is allowed to edit patient for this company
            if (!$user->isAdmin() && !$user->getCompanies()->contains($company)) {
                $this->addFlash('error', 'Você não tem permissão para editar pacientes nesta empresa.');
                return $this->redirectToRoute('admin_patients_edit', ['id' => $patient->getId()]);
            }

            $patient->setCompany($company);
            $patient->setName((string) $request->request->get('name'));
            $patient->setEmail((string) $request->request->get('email') ?: null);
            $patient->setPhone((string) $request->request->get('phone') ?: null);
            $patient->setCpf((string) $request->request->get('cpf') ?: null);
            $patient->setNoEmail((bool) $request->request->get('no_email', false));

            $birthday = $request->request->get('birthday');
            if ($birthday) {
                $patient->setBirthday(new \DateTimeImmutable($birthday));
            } else {
                $patient->setBirthday(null);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Paciente atualizado com sucesso.');

            return $this->redirectToRoute('admin_patients');
        }

        return $this->render('admin/patients/edit.html.twig', [
            'patient' => $patient,
            'companies' => $companies,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_patients_delete')]
    #[IsGranted(['ROLE_ADMIN', 'ROLE_OPERATOR'])]
    public function delete(Patient $patient): Response
    {
        $user = $this->getUser();
        if (!$user->isAdmin() && !$user->getCompanies()->contains($patient->getCompany())) {
            $this->addFlash('error', 'Você não tem permissão para excluir este paciente.');
            return $this->redirectToRoute('admin_patients');
        }

        $this->entityManager->remove($patient);
        $this->entityManager->flush();

        $this->addFlash('success', 'Paciente excluído com sucesso.');

        return $this->redirectToRoute('admin_patients');
    }
}