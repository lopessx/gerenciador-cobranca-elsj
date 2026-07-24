<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/users')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('', name: 'user_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $users = $this->em->getRepository(User::class)->findAll();
        $data = array_map(fn(User $user) => $user->toArray(), $users);
        return $this->json($data);
    }

    #[Route('', name: 'user_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email'] ?? '');
        $user->setName($data['name'] ?? '');
        $user->setCpf($data['cpf'] ?? null);
        $user->setRole(Role::from($data['role'] ?? 'operator'));

        $plainPassword = $data['password'] ?? '';
        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $this->em->persist($user);
        $this->em->flush();

        return $this->json($user->toArray(), 201);
    }

    #[Route('/{id}', name: 'user_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $user = $this->em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }
        return $this->json($user->toArray());
    }

    #[Route('/{id}', name: 'user_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $user = $this->em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (isset($data['password']) && $data['password'] !== '') {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }
        if (isset($data['name'])) {
            $user->setName($data['name']);
        }
        if (isset($data['cpf'])) {
            $user->setCpf($data['cpf']);
        }
        if (isset($data['role'])) {
            $user->setRole(Role::from($data['role']));
        }

        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $this->em->flush();

        return $this->json($user->toArray());
    }

    #[Route('/{id}', name: 'user_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $user = $this->em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $this->em->remove($user);
        $this->em->flush();

        return $this->json(null, 204);
    }
}
