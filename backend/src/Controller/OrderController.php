<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Client;
use App\Entity\OrderInstallment;
use App\Entity\PaymentSettings;
use App\Payment\GatewayRegistry;
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
        private GatewayRegistry $gatewayRegistry,
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

        $client = $this->em->getRepository(Client::class)->find($data['client_id'] ?? 0);
        if (!$client) {
            return $this->json(['error' => 'Client not found'], 404);
        }

        $order = new Order();
        $order->setAmount($data['amount'] ?? '0');
        $order->setInstallments((int) ($data['installments'] ?? 1));
        $order->setPaymentMethod($data['payment_method'] ?? 'paghiper_boleto');
        $order->setClient($client);

        $errors = $this->validator->validate($order);
        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], 400);
        }

        $this->em->persist($order);
        $this->em->flush();

        $installmentsGenerated = [];
        if ($order->getPaymentMethod() === 'paghiper_boleto') {
            $installmentsGenerated = $this->generateBankSlips($order);
        }

        $result = $order->toArray();
        $result['installments_generated'] = count($installmentsGenerated);

        return $this->json($result, 201);
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
        if (isset($data['payment_method'])) {
            $order->setPaymentMethod($data['payment_method']);
        }
        if (isset($data['client_id'])) {
            $client = $this->em->getRepository(Client::class)->find($data['client_id']);
            if (!$client) {
                return $this->json(['error' => 'Client not found'], 404);
            }
            $order->setClient($client);
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

    #[Route('/{id}/generate-bank-slips', name: 'order_generate_bank_slips', methods: ['POST'])]
    public function generateBankSlipsAction(int $id): JsonResponse
    {
        $order = $this->em->getRepository(Order::class)->find($id);
        if (!$order) {
            return $this->json(['error' => 'Order not found'], 404);
        }

        if ($order->getPaymentMethod() !== 'paghiper_boleto') {
            return $this->json(['error' => 'Geracao de boletos disponivel apenas para paghiper_boleto'], 400);
        }

        $installmentsGenerated = $this->generateBankSlips($order);

        return $this->json([
            'message' => count($installmentsGenerated) . ' boleto(s) gerado(s) com sucesso.',
            'installments_data' => $order->toArray()['installments_data'],
        ]);
    }

    private function generateBankSlips(Order $order): array
    {
        $gateway = $this->gatewayRegistry->get('paghiper_boleto');

        $settingsRepo = $this->em->getRepository(PaymentSettings::class);
        $settingsEntities = $settingsRepo->findBy(['paymentGateway' => 'paghiper_boleto']);

        $config = [];
        foreach ($settingsEntities as $setting) {
            $config[$setting->getOptionName()] = $setting->getValue();
        }

        $results = $gateway->processInstallments($order, $config);

        $installmentsGenerated = [];

        foreach ($results as $index => $result) {
            $parcelaNum = $index + 1;
            $totalParcelas = $order->getInstallments();

            $installment = new OrderInstallment();
            $installment->setOrder($order);
            $installment->setInstallmentNumber($parcelaNum);

            $dueDate = new \DateTime("+{$parcelaNum} months");
            $installment->setDueDate($dueDate);

            $totalAmount = (float) $order->getAmount();
            $parcelaAmount = round($totalAmount / $totalParcelas, 2);
            $installment->setAmount((string) $parcelaAmount);

            if ($result->isSuccess()) {
                $installment->setGatewayTransactionId($result->getGatewayTransactionId());
                $installment->setGatewayStatus($result->getGatewayStatus() ?? 'PENDING');
                $installment->setBankSlipUrl($result->getBankSlipUrl());
                $installment->setBankSlipBarcode($result->getBankSlipBarcode());
            } else {
                $installment->setGatewayStatus('ERROR');
            }

            $this->em->persist($installment);
            $installmentsGenerated[] = $installment;
        }

        $this->em->flush();

        return $installmentsGenerated;
    }
}
