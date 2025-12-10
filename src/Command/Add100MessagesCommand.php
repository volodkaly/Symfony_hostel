<?php

namespace App\Command;

use App\Entity\Message;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Repository\UserRepository;
use Faker\Factory;

#[AsCommand(
    name: 'add100Messages',
    description: 'Mocking 100 messages',
)]
class Add100MessagesCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, private LoggerInterface $logger, private UserRepository $userRepository)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $faker = Factory::create();
        for ($i = 1; $i <= 100; $i++) {
            $senders = $this->userRepository->findAll();
            $recipients = $this->userRepository->findAll();
            $sender = $senders[array_rand($senders)];
            $recipient = $recipients[array_rand($recipients)];


            $message = new Message();
            $message->setContent($faker->text());

            $message->setSender($sender);
            $message->setRecipient($recipient);

            $this->em->persist($message);


            echo 'Message added: ' . PHP_EOL . $message->getContent() . PHP_EOL . 'sender: ' . $message->getSender()->getName() . PHP_EOL . 'recipient: ' . $message->getRecipient()->getName() . PHP_EOL;
            $this->logger->info('custom log: 100 messages were mocked');
        }
        $this->em->flush();
        $this->em->clear();
        return Command::SUCCESS;
    }
}
