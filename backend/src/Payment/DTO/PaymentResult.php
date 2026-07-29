<?php

namespace App\Payment\DTO;

use App\Entity\Order;

class PaymentResult
{
    public function __construct(
        private bool $success,
        private Order $order,
        private ?string $gatewayTransactionId = null,
        private ?string $gatewayStatus = null,
        private ?string $bankSlipUrl = null,
        private ?string $bankSlipBarcode = null,
        private ?string $pixQrCode = null,
        private ?string $pixCopyPaste = null,
        private ?string $errorMessage = null,
        private array $rawResponse = [],
    ) {
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getGatewayTransactionId(): ?string
    {
        return $this->gatewayTransactionId;
    }

    public function getGatewayStatus(): ?string
    {
        return $this->gatewayStatus;
    }

    public function getBankSlipUrl(): ?string
    {
        return $this->bankSlipUrl;
    }

    public function getBankSlipBarcode(): ?string
    {
        return $this->bankSlipBarcode;
    }

    public function getPixQrCode(): ?string
    {
        return $this->pixQrCode;
    }

    public function getPixCopyPaste(): ?string
    {
        return $this->pixCopyPaste;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getRawResponse(): array
    {
        return $this->rawResponse;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'order_id' => $this->order->getOrderId(),
            'gateway_transaction_id' => $this->gatewayTransactionId,
            'gateway_status' => $this->gatewayStatus,
            'bank_slip_url' => $this->bankSlipUrl,
            'bank_slip_barcode' => $this->bankSlipBarcode,
            'pix_qr_code' => $this->pixQrCode,
            'pix_copy_paste' => $this->pixCopyPaste,
            'error_message' => $this->errorMessage,
        ];
    }

    public static function success(
        Order $order,
        string $gatewayTransactionId,
        string $gatewayStatus,
        ?string $bankSlipUrl = null,
        ?string $bankSlipBarcode = null,
        ?string $pixQrCode = null,
        ?string $pixCopyPaste = null,
        array $rawResponse = [],
    ): self {
        return new self(
            success: true,
            order: $order,
            gatewayTransactionId: $gatewayTransactionId,
            gatewayStatus: $gatewayStatus,
            bankSlipUrl: $bankSlipUrl,
            bankSlipBarcode: $bankSlipBarcode,
            pixQrCode: $pixQrCode,
            pixCopyPaste: $pixCopyPaste,
            rawResponse: $rawResponse,
        );
    }

    public static function failure(Order $order, string $errorMessage, array $rawResponse = []): self
    {
        return new self(
            success: false,
            order: $order,
            errorMessage: $errorMessage,
            rawResponse: $rawResponse,
        );
    }
}