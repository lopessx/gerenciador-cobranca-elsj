<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/companies')]
class CompanyController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('', name: 'admin_companies')]
    public function index(): Response
    {
        $companies = $this->entityManager->getRepository(Company::class)->findBy([], ['name' => 'ASC']);

        return $this->render('admin/companies/index.html.twig', [
            'companies' => $companies,
        ]);
    }

    #[Route('/new', name: 'admin_companies_new')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $company = new Company();
            $company->setName((string) $request->request->get('name'));
            $company->setRazaoSocial((string) $request->request->get('razao_social'));
            $company->setEmail((string) $request->request->get('email'));
            $company->setMinEntryAmount((int) $request->request->get('min_entry_amount', 0));

            $this->entityManager->persist($company);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_companies');
        }

        return $this->render('admin/companies/new.html.twig');
    }

    #[Route('/{id}/edit', name: 'admin_companies_edit')]
    public function edit(Company $company, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $company->setName((string) $request->request->get('name'));
            $company->setRazaoSocial((string) $request->request->get('razao_social'));
            $company->setEmail((string) $request->request->get('email'));
            $company->setMinEntryAmount((int) $request->request->get('min_entry_amount', 0));

            $this->entityManager->flush();

            return $this->redirectToRoute('admin_companies');
        }

        return $this->render('admin/companies/edit.html.twig', [
            'company' => $company,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_companies_delete')]
    public function delete(Company $company): Response
    {
        $this->entityManager->remove($company);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_companies');
    }
}
