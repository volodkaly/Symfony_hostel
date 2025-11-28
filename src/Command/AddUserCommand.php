<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'addUser',
    description: 'mocking 100 users (not admins)',
)]
class AddUserCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {


        $user = (new User())->setName('admin')->setEmail('admin')->setPassword(password_hash('admin', PASSWORD_BCRYPT))->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

        $this->em->persist($user);
        $this->em->flush();

        echo 'User added: ' . PHP_EOL . $user->getName() . PHP_EOL . 'email: ' . $user->getEmail() . PHP_EOL;


        return Command::SUCCESS;
    }
}
