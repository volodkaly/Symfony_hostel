<?php

namespace App\Command;

use App\Entity\Room;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Dom\Entity;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'go',
    description: 'Add a short description for your command',
)]
class GoCommand extends Command
{
    public function __construct(private RoomRepository $roomRepository, private UserRepository $users, private EntityManagerInterface $em)
    {
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {


        $room = new Room();

        $room->setName('Eleventh Room');
        $room->setCapacity(2);
        $room->setPrice(180);

        $this->em->persist($room);
        $this->em->flush();
        // тільки name для Room
        // $rooms = $this->roomRepository
        //     ->createQueryBuilder('r')
        //     ->select('r.name')
        //     ->getQuery()
        //     ->getScalarResult();

        // dump($rooms);

        // // тільки username для Users
        // $users = $this->users
        //     ->createQueryBuilder('u')
        //     ->select('u.name')
        //     ->getQuery()
        //     ->getResult();

        // dump($users);
        return Command::SUCCESS;
    }
}
