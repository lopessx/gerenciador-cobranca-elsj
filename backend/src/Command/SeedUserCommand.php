<?php

namespace App\Command;

use App\Entity\User;
use App\Enum\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed-user',
    description: 'Cria o usuário padrão de testes (admin) caso não exista',
)]
class SeedUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = $_ENV['DEFAULT_ADMIN_EMAIL'] ?? 'admin@admin.com';
        $plainPassword = $_ENV['DEFAULT_ADMIN_PASSWORD'] ?? '123456789';

        $existing = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existing) {
            $io->warning("Usuário '{$email}' já existe. Nenhuma ação foi realizada.");
            return Command::SUCCESS;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setName('Administrador');
        $user->setRole(Role::Admin);
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        $this->em->persist($user);
        $this->em->flush();

        $io->success("Usuário padrão criado com sucesso: {$email}");

        return Command::SUCCESS;
    }
}
