<?php

namespace App\Controller;

use App\Entity\PaymentMethod;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/payment-methods')]
class PaymentMethodController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'payment_method_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $methods = $this->em->getRepository(PaymentMethod::class)->findAll();
        $data = array_map(fn(PaymentMethod $m) => $m->toArray(), $methods);
        return $this->json($data);
    }

    #[Route('', name: 'payment_method_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $method = new PaymentMethod();
        $method->setName($data['name'] ?? '');
        $method->setApiKey($data['api_key'] ?? '');
        $method->setSecret($data['secret'] ?? '');

        $errors = $this->validator->validate($method);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $this->em->persist($method);
        $this->em->flush();

        return $this->json($method->toArray(), 201);
    }

    #[Route('/{id}', name: 'payment_method_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $method = $this->em->getRepository(PaymentMethod::class)->find($id);
        if (!$method) {
            return $this->json(['error' => 'Payment method not found'], 404);
        }
        return $this->json($method->toArray());
    }

    #[Route('/{id}', name: 'payment_method_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $method = $this->em->getRepository(PaymentMethod::class)->find($id);
        if (!$method) {
            return $this->json(['error' => 'Payment method not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $method->setName($data['name']);
        }
        if (isset($data['api_key'])) {
            $method->setApiKey($data['api_key']);
        }
        if (isset($data['secret'])) {
            $method->setSecret($data['secret']);
        }

        $errors = $this->validator->validate($method);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $this->em->flush();

        return $this->json($method->toArray());
    }

    #[Route('/{id}', name: 'payment_method_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $method = $this->em->getRepository(PaymentMethod::class)->find($id);
        if (!$method) {
            return $this->json(['error' => 'Payment method not found'], 404);
        }

        $this->em->remove($method);
        $this->em->flush();

        return $this->json(null, 204);
    }
}