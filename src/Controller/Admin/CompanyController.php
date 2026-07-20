<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/companies')]
class CompanyController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('', name: 'admin_companies')]
    #[IsGranted('ROLE_ADMIN')]
    public function index(): Response
    {
        $companies = $this->entityManager->getRepository(Company::class)->findBy([], ['name' => 'ASC']);

        return $this->render('admin/companies/index.html.twig', [
            'companies' => $companies,
        ]);
    }

    #[Route('/new', name: 'admin_companies_new')]
    #[IsGranted('ROLE_ADMIN')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $company = new Company();
            $company->setName((string) $request->request->get('name'));
            $company->setCnpj((string) $request->request->get('cnpj') ?: null);
            $company->setEmail((string) $request->request->get('email') ?: null);
            $company->setDefaultPatientEmail((string) $request->request->get('default_patient_email') ?: null);

            $this->entityManager->persist($company);
            $this->entityManager->flush();

            $this->addFlash('success', 'Empresa cadastrada com sucesso.');

            return $this->redirectToRoute('admin_companies');
        }

        return $this->render('admin/companies/new.html.twig');
    }

    #[Route('/{id}/edit', name: 'admin_companies_edit')]
    #[IsGranted('ROLE_ADMIN')]
    public function edit(Company $company, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $company->setName((string) $request->request->get('name'));
            $company->setCnpj((string) $request->request->get('cnpj') ?: null);
            $company->setEmail((string) $request->request->get('email') ?: null);
            $company->setDefaultPatientEmail((string) $request->request->get('default_patient_email') ?: null);

            $this->entityManager->flush();

            $this->addFlash('success', 'Empresa atualizada com sucesso.');

            return $this->redirectToRoute('admin_companies');
        }

        return $this->render('admin/companies/edit.html.twig', [
            'company' => $company,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_companies_delete')]
    #[IsGranted('ROLE_ADMIN')]
    public function delete(Company $company): Response
    {
        $this->entityManager->remove($company);
        $this->entityManager->flush();

        $this->addFlash('success', 'Empresa excluída com sucesso.');

        return $this->redirectToRoute('admin_companies');
    }
}