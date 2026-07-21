<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/users')]
#[IsGranted('ROLE_ADMIN')]
class UserController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('', name: 'admin_users')]
    public function index(): Response
    {
        $users = $this->entityManager->getRepository(User::class)->findBy([], ['name' => 'ASC']);

        return $this->render('admin/users/index.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/new', name: 'admin_users_new')]
    public function new(Request $request): Response
    {
        $companies = $this->entityManager->getRepository(Company::class)->findBy([], ['name' => 'ASC']);

        if ($request->isMethod('POST')) {
            $user = new User();
            $user->setName((string) $request->request->get('name'));
            $user->setEmail((string) $request->request->get('email'));
            $user->setRole((string) $request->request->get('role', User::ROLE_OPERATOR));
            $user->setMustChangePwd(true);

            $password = $request->request->get('password');
            if ($password) {
                $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            }

            // Associate companies
            $companyIds = $request->request->all('company_ids') ?? [];
            foreach ($companyIds as $companyId) {
                $company = $this->entityManager->getRepository(Company::class)->find($companyId);
                if ($company) {
                    $user->addCompany($company);
                }
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Usuário cadastrado com sucesso.');

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/new.html.twig', [
            'companies' => $companies,
            'roles' => User::ROLES_LABELS,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_users_edit')]
    public function edit(User $user, Request $request): Response
    {
        $companies = $this->entityManager->getRepository(Company::class)->findBy([], ['name' => 'ASC']);

        if ($request->isMethod('POST')) {
            $user->setName((string) $request->request->get('name'));
            $user->setEmail((string) $request->request->get('email'));
            $user->setRole((string) $request->request->get('role', User::ROLE_OPERATOR));

            $password = $request->request->get('password');
            if ($password) {
                $user->setPassword($this->passwordHasher->hashPassword($user, $password));
                $user->setMustChangePwd(true);
            }

            // Sync companies
            foreach ($user->getCompanies() as $company) {
                $user->removeCompany($company);
            }
            $companyIds = $request->request->all('company_ids') ?? [];
            foreach ($companyIds as $companyId) {
                $company = $this->entityManager->getRepository(Company::class)->find($companyId);
                if ($company) {
                    $user->addCompany($company);
                }
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Usuário atualizado com sucesso.');

            return $this->redirectToRoute('admin_users');
        }

        return $this->render('admin/users/edit.html.twig', [
            'user' => $user,
            'companies' => $companies,
            'roles' => User::ROLES_LABELS,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_users_delete')]
    public function delete(User $user): Response
    {
        if ($user === $this->getUser()) {
            $this->addFlash('error', 'Você não pode excluir seu próprio usuário.');
            return $this->redirectToRoute('admin_users');
        }

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->addFlash('success', 'Usuário excluído com sucesso.');

        return $this->redirectToRoute('admin_users');
    }
}