<?php

namespace App\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/clients')]
class ClientController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'client_list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $qb = $this->em->getRepository(Client::class)->createQueryBuilder('c');

        $q = $request->query->get('q');
        if ($q) {
            $qb
                ->where('c.name LIKE :q')
                ->orWhere('c.cpf LIKE :q')
                ->setParameter('q', '%' . $q . '%');
        }

        $clients = $qb->getQuery()->getResult();
        $data = array_map(fn(Client $client) => $client->toArray(), $clients);
        return $this->json($data);
    }

    #[Route('/{id}', name: 'client_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $client = $this->em->getRepository(Client::class)->find($id);
        if (!$client) {
            return $this->json(['error' => 'Client not found'], 404);
        }
        return $this->json($client->toArray());
    }

    #[Route('', name: 'client_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $client = new Client();
        $client->setName($data['name'] ?? '');
        $client->setCpf($data['cpf'] ?? '');
        $client->setEmail($data['email'] ?? null);
        $client->setPhone($data['phone'] ?? null);
        $client->setAddress($data['address'] ?? null);

        $errors = $this->validator->validate($client);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $this->em->persist($client);
        $this->em->flush();

        return $this->json($client->toArray(), 201);
    }

    #[Route('/{id}', name: 'client_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $client = $this->em->getRepository(Client::class)->find($id);
        if (!$client) {
            return $this->json(['error' => 'Client not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $client->setName($data['name']);
        }
        if (isset($data['cpf'])) {
            $client->setCpf($data['cpf']);
        }
        if (isset($data['email'])) {
            $client->setEmail($data['email']);
        }
        if (isset($data['phone'])) {
            $client->setPhone($data['phone']);
        }
        if (array_key_exists('address', $data)) {
            $client->setAddress($data['address']);
        }

        $errors = $this->validator->validate($client);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $this->em->flush();

        return $this->json($client->toArray());
    }

    #[Route('/{id}', name: 'client_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $client = $this->em->getRepository(Client::class)->find($id);
        if (!$client) {
            return $this->json(['error' => 'Client not found'], 404);
        }

        $this->em->remove($client);
        $this->em->flush();

        return $this->json(null, 204);
    }
}