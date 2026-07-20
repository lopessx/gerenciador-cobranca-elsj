<?php

namespace App\Service;

use App\Entity\Billing;
use Dompdf\Dompdf;
use Dompdf\Options;

class BillingPdfService
{
    private string $uploadDir;

    public function __construct(string $projectDir = '')
    {
        $this->uploadDir = $projectDir . '/public/uploads/billings';
    }

    public function generateBillingPdf(Billing $billing): string
    {
        $html = $this->renderHtml($billing);

        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = sprintf('cobranca-%s.pdf', $billing->getId());
        $filepath = $this->uploadDir . '/' . $filename;

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        file_put_contents($filepath, $dompdf->output());

        $billing->setPdfPath($filepath);

        return $filepath;
    }

    private function renderHtml(Billing $billing): string
    {
        $patient = $billing->getPatient();
        $company = $billing->getCompany();
        $installments = $billing->getInstallments();

        $rows = '';
        foreach ($installments as $inst) {
            $rows .= sprintf(
                '<tr>
                    <td style="padding:8px;border:1px solid #4d4635;text-align:center;">%d</td>
                    <td style="padding:8px;border:1px solid #4d4635;text-align:center;">%s</td>
                    <td style="padding:8px;border:1px solid #4d4635;text-align:right;">R$ %s</td>
                </tr>',
                $inst->getInstallmentNumber(),
                $inst->getDueDate()->format('d/m/Y'),
                number_format($inst->getAmount() / 100, 2, ',', '.')
            );
        }

        $entryFormatted = number_format($billing->getEntryAmount() / 100, 2, ',', '.');
        $totalFormatted = number_format($billing->getTotalAmount() / 100, 2, ',', '.');
        $remainingFormatted = number_format(($billing->getTotalAmount() - $billing->getEntryAmount()) / 100, 2, ',', '.');

        $patientEmail = $patient->getEffectiveEmail() ?? '—';

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: Helvetica, Arial, sans-serif; color: #E3E2E7; background: #121317; margin: 0; padding: 40px; }
    .header { border-bottom: 2px solid #ecc242; padding-bottom: 20px; margin-bottom: 30px; }
    .header h1 { color: #fff1d4; font-size: 24px; margin: 0; text-transform: uppercase; letter-spacing: 2px; }
    .header p { color: #9a907b; font-size: 12px; margin: 5px 0 0; }
    .info-grid { display: flex; gap: 40px; margin-bottom: 30px; }
    .info-grid div { flex: 1; }
    .info-grid label { color: #9a907b; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 4px; }
    .info-grid span { color: #E3E2E7; font-size: 16px; }
    .amounts { display: flex; gap: 20px; margin-bottom: 30px; }
    .amount-box { background: #1e1f24; padding: 16px; flex: 1; border-left: 3px solid #ecc242; }
    .amount-box label { color: #9a907b; font-size: 10px; text-transform: uppercase; letter-spacing: 1px; display: block; }
    .amount-box .value { color: #fff1d4; font-size: 22px; font-weight: bold; margin-top: 4px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th { background: #1e1f24; color: #fff1d4; padding: 10px 8px; border: 1px solid #4d4635; font-size: 12px; text-transform: uppercase; letter-spacing: 1px; }
    td { color: #E3E2E7; font-size: 13px; }
    .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #4d4635; text-align: center; color: #9a907b; font-size: 11px; }
</style>
</head>
<body>
    <div class="header">
        <h1>Cobri ELSJ</h1>
        <p>SISTEMAS DE FATURAMENTO DE PRECISÃO</p>
    </div>

    <div class="info-grid">
        <div>
            <label>Paciente</label>
            <span>{$patient->getName()}</span>
        </div>
        <div>
            <label>E-mail</label>
            <span>{$patientEmail}</span>
        </div>
        <div>
            <label>Empresa</label>
            <span>{$company->getName()}</span>
        </div>
        <div>
            <label>CPF</label>
            <span>{$patient->getCpf()}</span>
        </div>
    </div>

    <div class="amounts">
        <div class="amount-box">
            <label>Valor Total</label>
            <div class="value">R$ {$totalFormatted}</div>
        </div>
        <div class="amount-box">
            <label>Entrada</label>
            <div class="value">R$ {$entryFormatted}</div>
        </div>
        <div class="amount-box">
            <label>Saldo Parcelado</label>
            <div class="value">R$ {$remainingFormatted}</div>
        </div>
    </div>

    <h3 style="color:#fff1d4;font-size:14px;text-transform:uppercase;letter-spacing:1px;">Cronograma de Parcelas</h3>
    <table>
        <thead>
            <tr>
                <th>Parcela</th>
                <th>Vencimento</th>
                <th>Valor</th>
            </tr>
        </thead>
        <tbody>
            {$rows}
        </tbody>
    </table>

    <div class="footer">
        <p>© 2024 Cobri ELSJ — Sistemas de Faturamento de Precisão</p>
        <p>Documento gerado automaticamente pelo sistema de gestão de cobranças.</p>
    </div>
</body>
</html>
HTML;
    }
}