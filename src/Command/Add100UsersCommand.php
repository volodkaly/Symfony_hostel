<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'add100Users',
    description: 'mocking 100 users (not admins)',
)]
class Add100UsersCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, private LoggerInterface $logger)
    {
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        for ($i = 0; $i < 100; $i++) {

            $user = (new User())->setName('User' . rand(20, 100))->setEmail('user' . random_int(10000, 100000) . '@example.com')->setPassword(password_hash(1, PASSWORD_BCRYPT))->setRoles(['ROLE_USER']);

            $this->em->persist($user);
            $this->em->flush();

            echo 'User added: ' . PHP_EOL . $user->getName() . PHP_EOL . 'email: ' . $user->getEmail() . PHP_EOL;
        }

        $this->logger->info('custom log: users were mocked');
        return Command::SUCCESS;
    }
}