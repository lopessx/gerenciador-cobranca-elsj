<?php

namespace App\Payment\Gateway;

use App\Entity\Order;
use App\Payment\AbstractPaymentGateway;
use App\Payment\DTO\PaymentResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PagHiperBoletoGateway extends AbstractPaymentGateway
{
    private const BASE_URL = 'https://api.paghiper.com';
    private const TRANSACTION_ENDPOINT = '/transaction/create/';
    private const CONSULT_ENDPOINT = '/transaction/notification/';
    private const CANCEL_ENDPOINT = '/transaction/cancel/';

    public function getName(): string
    {
        return 'paghiper_boleto';
    }

    public function getLabel(): string
    {
        return 'PagHiper - Boleto Bancário';
    }

    public function getConfigFields(): array
    {
        return [
            [
                'name' => 'api_key',
                'label' => 'API Key (apikey)',
                'type' => 'password',
                'required' => true,
            ],
            [
                'name' => 'token',
                'label' => 'Token',
                'type' => 'password',
                'required' => true,
            ],
            [
                'name' => 'days_due_date',
                'label' => 'Dias para vencimento do boleto (usado para pedido avulso)',
                'type' => 'number',
                'required' => false,
            ],
            [
                'name' => 'early_payment_discounts_days',
                'label' => 'Dias para desconto por pagamento antecipado',
                'type' => 'number',
                'required' => false,
            ],
            [
                'name' => 'early_payment_discounts_perc',
                'label' => 'Percentual de desconto por antecipação',
                'type' => 'number',
                'required' => false,
            ],
            [
                'name' => 'per_day_interest',
                'label' => 'Juros por dia de atraso (%) — use true para ativar',
                'type' => 'checkbox',
                'required' => false,
            ],
            [
                'name' => 'open_after_day_due',
                'label' => 'Dias após vencimento que o boleto continua aberto',
                'type' => 'number',
                'required' => false,
            ],
        ];
    }

    public function supportsInstallments(): bool
    {
        return true;
    }

    public function getSupportedPaymentMethods(): array
    {
        return ['boleto'];
    }

    /**
     * Processa uma única cobrança (boleto avulso, sem carnê).
     */
    public function processOrder(Order $order, array $config): PaymentResult
    {
        $apiKey = $config['api_key'] ?? null;
        $token = $config['token'] ?? null;

        if (empty($apiKey) || empty($token)) {
            return PaymentResult::failure($order, 'Credenciais PagHiper não configuradas: api_key e token são obrigatórios.');
        }

        $daysDueDate = (int) ($config['days_due_date'] ?? 3);
        $dueDate = (new \DateTime("+{$daysDueDate} days"))->format('Y-m-d');

        $payload = $this->buildPayload($order, $config, 1, 1, $dueDate);

        return $this->sendTransactionRequest($order, $payload);
    }

    /**
     * Processa múltiplas parcelas de um carnê — loop de 1 a N meses.
     *
     * @return PaymentResult[]
     */
    public function processInstallments(Order $order, array $config): array
    {
        $apiKey = $config['api_key'] ?? null;
        $token = $config['token'] ?? null;

        if (empty($apiKey) || empty($token)) {
            return [PaymentResult::failure($order, 'Credenciais PagHiper não configuradas: api_key e token são obrigatórios.')];
        }

        $totalInstallments = $order->getInstallments();
        $results = [];

        for ($parcela = 1; $parcela <= $totalInstallments; $parcela++) {
            // Vencimento: adiciona N meses à data atual
            $dueDate = (new \DateTime("+{$parcela} months"))->format('Y-m-d');

            $payload = $this->buildPayload($order, $config, $parcela, $totalInstallments, $dueDate);

            $result = $this->sendTransactionRequest($order, $payload);
            $results[] = $result;

            if (!$result->isSuccess()) {
                $this->logger->error('Carnê PagHiper — falha ao gerar parcela', [
                    'parcela' => $parcela,
                    'total' => $totalInstallments,
                    'error' => $result->getErrorMessage(),
                ]);
                break;
            }
        }

        return $results;
    }

    public function handleWebhook(Request $request, array $config): Response
    {
        $payload = json_decode($request->getContent(), true);

        if (!$payload) {
            $this->logger->warning('Webhook PagHiper recebido sem payload válido');
            return new Response('Payload inválido', 400);
        }

        $this->logger->info('Webhook PagHiper recebido', ['payload' => $payload]);

        $transactionId = $payload['transaction_id'] ?? null;
        $notificationId = $payload['notification_id'] ?? null;

        if (!$transactionId || !$notificationId) {
            return new Response('transaction_id e notification_id obrigatórios', 400);
        }

        try {
            $token = $config['token'] ?? '';
            $apiKey = $config['api_key'] ?? '';

            $this->httpClient->request('POST', self::BASE_URL . self::CONSULT_ENDPOINT, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'apiKey' => $apiKey,
                    'token' => $token,
                    'transaction_id' => $transactionId,
                    'notification_id' => $notificationId,
                ],
                'timeout' => 30,
            ]);

            $this->logger->info('Webhook PagHiper — status consultado', [
                'transaction_id' => $transactionId,
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Erro ao validar webhook PagHiper', [
                'exception' => $e->getMessage(),
            ]);
            return new Response('Erro ao validar webhook', 500);
        }

        return new Response('OK', 200);
    }

    public function verifyStatus(Order $order, array $config): string
    {
        $token = $config['token'] ?? '';
        $apiKey = $config['api_key'] ?? '';

        try {
            $response = $this->httpClient->request('POST', self::BASE_URL . self::CONSULT_ENDPOINT, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'apiKey' => $apiKey,
                    'token' => $token,
                    'transaction_id' => $order->getOrderId(),
                ],
                'timeout' => 30,
            ]);

            $body = $response->toArray(false);
            $status = $body['status_request']['status'] ?? 'pending';

            $this->logger->info('PagHiper verifyStatus', [
                'order_id' => $order->getOrderId(),
                'raw_status' => $status,
            ]);

            return $this->mapStatus($status);
        } catch (\Throwable $e) {
            $this->logger->error('Erro ao verificar status PagHiper', [
                'exception' => $e->getMessage(),
                'order_id' => $order->getOrderId(),
            ]);
            return 'ERROR';
        }
    }
    // ---------------------------------------------------------------------------
    // Métodos privados
    // ---------------------------------------------------------------------------

    private function sendTransactionRequest(Order $order, array $payload): PaymentResult
    {
        try {
            $response = $this->httpClient->request('POST', self::BASE_URL . self::TRANSACTION_ENDPOINT, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
                'timeout' => 30,
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->toArray(false);

            $this->logger->info('PagHiper transaction create response', [
                'status_code' => $statusCode,
                'order_id' => $payload['order_id'] ?? null,
            ]);

            if ($statusCode !== 201 && $statusCode !== 200) {
                $errorMsg = $body['message'] ?? $body['create_request']['response_message'] ?? 'Erro desconhecido ao criar cobrança';
                return PaymentResult::failure($order, $errorMsg, $body);
            }

            $createRequest = $body['create_request'] ?? [];

            if (($createRequest['result'] ?? '') !== 'reject') {
                return PaymentResult::success(
                    order: $order,
                    gatewayTransactionId: $createRequest['transaction_id'] ?? '',
                    gatewayStatus: $this->mapStatus($createRequest['status'] ?? 'pending'),
                    bankSlipUrl: $createRequest['bank_slip']['url_slip']
                        ?? $createRequest['bank_slip']['url_slipping'] ?? null,
                    bankSlipBarcode: $createRequest['bank_slip']['digitable_line'] ?? null,
                    rawResponse: $body,
                );
            }

            return PaymentResult::failure(
                $order,
                $createRequest['response_message'] ?? 'Transação rejeitada pela PagHiper',
                $body,
            );
        } catch (\Throwable $e) {
            $this->logger->error('Erro ao processar cobrança PagHiper', [
                'exception' => $e->getMessage(),
                'order_id' => $payload['order_id'] ?? null,
            ]);
            return PaymentResult::failure($order, 'Falha na comunicação com PagHiper: ' . $e->getMessage());
        }
    }


    private function buildPayload(Order $order, array $config, int $installmentNumber, int $totalInstallments, string $dueDate): array
    {
        $apiKey = $config['api_key'] ?? '';
        $token = $config['token'] ?? '';
        $client = $order->getClient();

        $orderIdParcela = $order->getOrderId() . '-' . str_pad((string) $installmentNumber, 2, '0', STR_PAD_LEFT) . '/' . $totalInstallments;

        $totalAmount = (float) $order->getAmount();
        $parcelaAmount = round($totalAmount / $totalInstallments, 2);
        $amountInCents = (int) round($parcelaAmount * 100);

        $payload = [
            'apiKey' => $apiKey,
            'token' => $token,
            'order_id' => $orderIdParcela,
            'payer_email' => $client->getEmail() ?? '',
            'payer_name' => $client->getName() ?? '',
            'payer_cpf_cnpj' => $client->getCpf() ?? '',
            'payer_phone' => $client->getPhone() ?? '',
            'notification_url' => $config['notification_url'] ?? '',
            'discount_cents' => '',
            'shipping_price_cents' => '',
            'shipping_methods' => '',
            'fixed_description' => true,
            'type_bank_slip' => 'boletoA4',
            'days_due_date' => 0,
            'due_date' => $dueDate,
            'late_payment_fine' => '',
            'per_day_interest' => $config['per_day_interest'] ?? '',
            'early_payment_discounts_days' => $config['early_payment_discounts_days'] ?? '',
            'early_payment_discounts_perc' => $config['early_payment_discounts_perc'] ?? '',
            'open_after_day_due' => (int) ($config['open_after_day_due'] ?? 1),
        ];

        $descricao = $totalInstallments > 1
            ? "Mensalidade Carnê - Parcela {$installmentNumber}/{$totalInstallments}"
            : 'Cobrança #' . $order->getOrderId();

        $payload['items'] = [
            [
                'item_id' => (string) $installmentNumber,
                'description' => $descricao,
                'price_cents' => $amountInCents,
                'quantity' => 1,
            ],
        ];

        return $payload;
    }

    private function mapStatus(string $paghiperStatus): string
    {
        return match (mb_strtolower($paghiperStatus)) {
            'pending', 'awaiting_payment', 'boleto_emitido', 'processing' => 'PENDING',
            'paid', 'settled', 'completed', 'reserved', 'approved' => 'PAID',
            'canceled', 'refunded', 'cancelled', 'chargeback' => 'CANCELLED',
            'expired', 'overdue' => 'EXPIRED',
            'refused', 'reject', 'denied' => 'ERROR',
            default => 'PENDING',
        };
    }
}
