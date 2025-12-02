<?php

namespace App\Command;

use App\Entity\Room;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManager;
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
    name: 'addRoom',
    description: 'Mocking 1 room',
)]
class AddRoomCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, private LoggerInterface $logger)
    {
        parent::__construct();
    }



    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $room = (new Room())->setName('Room' . rand(20, 100))->setCapacity(rand(1, 4))->setPrice(rand(50, 1000));

        $this->em->persist($room);
        $this->em->flush();

        echo 'Room added: ' . PHP_EOL . $room->getName() . PHP_EOL . 'price: ' . $room->getPrice() . PHP_EOL . 'capacity: ' . $room->getCapacity() . PHP_EOL;
        $this->logger->info('custom log: 1 room was mocked');
        return Command::SUCCESS;
    }
}
