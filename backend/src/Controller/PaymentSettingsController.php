<?php

namespace App\Controller;

use App\Entity\PaymentSettings;
use App\Payment\GatewayRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/payment-settings')]
class PaymentSettingsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private GatewayRegistry $registry,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        // Schemas de todos os gateways registrados via tagged_iterator
        $schemas = $this->registry->getSchemas();

        // Configurações salvas no banco, agrupadas por gateway => optionName => value
        $settings = $this->em->getRepository(PaymentSettings::class)->findAll();
        $savedConfigs = [];

        foreach ($settings as $setting) {
            $gateway = $setting->getPaymentGateway()->value;
            $savedConfigs[$gateway][$setting->getOptionName()] = $setting->toArray();
        }

        return $this->json([
            'gateways' => $schemas,
            'settings' => $savedConfigs,
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function save(Request $request, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['settings']) || !is_array($data['settings'])) {
            return $this->json(['error' => 'Payload inválido. Esperado: { "settings": { "gateway_name": { "option": "value", ... }, ... } }'], Response::HTTP_BAD_REQUEST);
        }

        $this->em->beginTransaction();

        try {
            foreach ($data['settings'] as $gatewayName => $options) {
                if (!is_array($options)) {
                    continue;
                }

                foreach ($options as $optionName => $value) {
                    // Busca configuração existente para fazer upsert
                    $repo = $this->em->getRepository(PaymentSettings::class);
                    $existing = $repo->findOneBy([
                        'paymentGateway' => $gatewayName,
                        'optionName' => $optionName,
                    ]);

                    if ($existing) {
                        $existing->setValue($value !== '' ? (string) $value : null);
                    } else {
                        $gatewayEnum = \App\Enum\PaymentGateway::from($gatewayName);
                        $setting = new PaymentSettings();
                        $setting->setPaymentGateway($gatewayEnum);
                        $setting->setOptionName($optionName);
                        $setting->setValue($value !== '' ? (string) $value : null);
                        $this->em->persist($setting);
                    }
                }
            }

            $this->em->flush();
            $this->em->commit();

            return $this->json(['message' => 'Configurações salvas com sucesso.']);
        } catch (\Throwable $e) {
            $this->em->rollback();

            return $this->json(['error' => 'Erro ao salvar configurações: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}