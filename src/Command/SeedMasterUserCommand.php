<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

#[AsCommand(
    name: 'app:seed-master-user',
    description: 'Creates the first admin user from MASTER_EMAIL and MASTER_PASSWORD environment variables.',
)]
class SeedMasterUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PasswordHasherFactoryInterface $passwordHasherFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $_SERVER['MASTER_EMAIL'] ?? $_ENV['MASTER_EMAIL'] ?? null;
        $password = $_SERVER['MASTER_PASSWORD'] ?? $_ENV['MASTER_PASSWORD'] ?? null;

        if (null === $email || null === $password) {
            $io->error('MASTER_EMAIL and MASTER_PASSWORD must be defined in .env');

            return Command::FAILURE;
        }

        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (null !== $existingUser) {
            $io->warning(sprintf('Master user with email "%s" already exists.', $email));

            return Command::SUCCESS;
        }

        $hasher = $this->passwordHasherFactory->getPasswordHasher(User::class);

        $user = new User();
        $user->setEmail($email);
        $user->setName('Administrador Master');
        $user->setPassword($hasher->hash($password));
        $user->setRoles(['ROLE_ADMIN']);
        $user->setMustChangePwd(false);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf('Master user "%s" created successfully.', $email));

        return Command::SUCCESS;
    }
}