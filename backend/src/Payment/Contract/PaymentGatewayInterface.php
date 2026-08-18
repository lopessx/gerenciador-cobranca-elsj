<?php

namespace App\Payment\Contract;

use App\Entity\Order;
use App\Payment\DTO\PaymentResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface PaymentGatewayInterface
{
    /**
     * Retorna o nome único do gateway (ex: 'paghiper_boleto', 'asaas').
     */
    public function getName(): string;

    /**
     * Retorna o label amigável para exibição no front-end.
     */
    public function getLabel(): string;

    /**
     * Retorna os metadados dos campos exigidos para que o Front-end (Vue/Quasar)
     * construa o formulário de configuração dinamicamente.
     *
     * Cada item deve conter: name, label, type, required
     */
    public function getConfigFields(): array;

    /**
     * Indica se o gateway suporta parcelamento.
     */
    public function supportsInstallments(): bool;

    /**
     * Retorna os métodos de pagamento suportados por este gateway.
     * Ex: ['boleto'], ['pix'], ['boleto', 'pix']
     */
    public function getSupportedPaymentMethods(): array;

    /**
     * Processa uma cobrança (Order) e retorna o resultado padronizado.
     *
     * @param Order $order  Entidade Order com os dados da cobrança
     * @param array $config Configuração do gateway para o tenant (ex: ['api_key' => '...', 'token' => '...'])
     */
    public function processOrder(Order $order, array $config): PaymentResult;

    /**
     * Processa múltiplas parcelas de um carnê e retorna os resultados.
     * Cada item do array de retorno contém o resultado de uma parcela.
     *
     * @param Order $order  Entidade Order com os dados da cobrança
     * @param array $config Configuração do gateway
     * @return PaymentResult[] Array de resultados, um por parcela
     */
    public function processInstallments(Order $order, array $config): array;

    /**
     * Trata o webhook recebido da adquirente, validando assinatura e atualizando status.
     */
    public function handleWebhook(Request $request, array $config): Response;

    /**
     * Consulta o status de uma cobrança diretamente na API do gateway.
     *
     * @param Order $order  Entidade Order
     * @param array $config Configuração do gateway para o tenant
     * @return string Status padronizado: 'PENDING', 'PAID', 'CANCELLED', 'EXPIRED', 'ERROR'
     */
    public function verifyStatus(Order $order, array $config): string;
}