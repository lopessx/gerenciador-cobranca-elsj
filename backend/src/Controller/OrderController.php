<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\PaymentMethod;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/orders')]
class OrderController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
    ) {
    }

    #[Route('', name: 'order_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $orders = $this->em->getRepository(Order::class)->findAll();
        $data = array_map(fn(Order $order) => $order->toArray(), $orders);
        return $this->json($data);
    }

    #[Route('', name: 'order_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $paymentMethod = $this->em->getRepository(PaymentMethod::class)->find($data['payment_method_id'] ?? 0);
        if (!$paymentMethod) {
            return $this->json(['error' => 'Payment method not found'], 404);
        }

        $user = $this->em->getRepository(User::class)->find($data['user_id'] ?? 0);
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }

        $order = new Order();
        $order->setAmount($data['amount'] ?? '0');
        $order->setInstallments($data['installments'] ?? 1);
        $order->setPaymentMethod($paymentMethod);
        $order->setUser($user);

        $errors = $this->validator->validate($order);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $this->em->persist($order);
        $this->em->flush();

        return $this->json($order->toArray(), 201);
    }

    #[Route('/{id}', name: 'order_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $order = $this->em->getRepository(Order::class)->find($id);
        if (!$order) {
            return $this->json(['error' => 'Order not found'], 404);
        }
        return $this->json($order->toArray());
    }

    #[Route('/{id}', name: 'order_update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $order = $this->em->getRepository(Order::class)->find($id);
        if (!$order) {
            return $this->json(['error' => 'Order not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['amount'])) {
            $order->setAmount($data['amount']);
        }
        if (isset($data['installments'])) {
            $order->setInstallments($data['installments']);
        }
        if (isset($data['payment_method_id'])) {
            $paymentMethod = $this->em->getRepository(PaymentMethod::class)->find($data['payment_method_id']);
            if (!$paymentMethod) {
                return $this->json(['error' => 'Payment method not found'], 404);
            }
            $order->setPaymentMethod($paymentMethod);
        }
        if (isset($data['user_id'])) {
            $user = $this->em->getRepository(User::class)->find($data['user_id']);
            if (!$user) {
                return $this->json(['error' => 'User not found'], 404);
            }
            $order->setUser($user);
        }

        $errors = $this->validator->validate($order);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $this->em->flush();

        return $this->json($order->toArray());
    }

    #[Route('/{id}', name: 'order_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $order = $this->em->getRepository(Order::class)->find($id);
        if (!$order) {
            return $this->json(['error' => 'Order not found'], 404);
        }

        $this->em->remove($order);
        $this->em->flush();

        return $this->json(null, 204);
    }
}