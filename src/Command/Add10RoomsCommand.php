<?php

namespace App\Command;

use App\Entity\Room;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'add10Rooms',
    description: 'Mocking 10 rooms',
)]
class Add10RoomsCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, private LoggerInterface $logger)
    {
        parent::__construct();
    }



    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        for ($i = 0; $i < 10; $i++) {
            $room = (new Room())->setName('Room' . rand(1, 100))->setCapacity(rand(1, 4))->setPrice(rand(50, 1000));

            $this->em->persist($room);

            echo 'Room added: ' . PHP_EOL . $room->getName() . PHP_EOL . 'price: ' . $room->getPrice() . PHP_EOL . 'capacity: ' . $room->getCapacity() . PHP_EOL;
            $this->logger->info('custom log: 10 rooms were mocked');
        }
        $this->em->flush();
        $this->em->clear();
        return Command::SUCCESS;

    }
}