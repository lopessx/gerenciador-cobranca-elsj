<?php

namespace App\EventListener;

use App\Filter\TenantFilter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST)]
class TenantContextListener
{
    public function __construct(
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->security->getUser();

        if (null === $user) {
            return;
        }

        if (\in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return;
        }

        $session = $event->getRequest()->getSession();
        $activeCompanyId = $session->get('active_company_id');

        if (null === $activeCompanyId) {
            return;
        }

        $filters = $this->entityManager->getFilters();
        if ($filters->isEnabled('tenant_filter')) {
            return;
        }

        $filter = $filters->enable('tenant_filter');
        \assert($filter instanceof TenantFilter);
        $filter->setParameter('current_company_id', $activeCompanyId);
    }
}
