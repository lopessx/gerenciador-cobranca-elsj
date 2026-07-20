<?php

namespace App\EventListener;

use App\Entity\Company;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: Company::class)]
class CompanyLifecycleListener
{
    public function preUpdate(Company $company, PreUpdateEventArgs $args): void
    {
        if (!$args->hasChangedField('isActive')) {
            return;
        }

        $newValue = $args->getNewValue('isActive');

        if (false !== $newValue) {
            return;
        }

        foreach ($company->getUsers() as $user) {
            $company->removeUser($user);
        }
    }
}