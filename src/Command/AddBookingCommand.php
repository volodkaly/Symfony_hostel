<?php

namespace App\Command;

use App\Entity\Booking;
use App\Repository\RoomRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'addBooking',
    description: 'mocking 100 bookings',
)]
class AddBookingCommand extends Command
{
    public function __construct(private EntityManagerInterface $entityManager, private UserRepository $userRepository, private RoomRepository $roomRepository)
    {
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {



        for ($i = 1; $i <= 1; $i++) {

            $customer = $this->userRepository->findOneBy(['email' => 'admin']);
            $rooms = $this->roomRepository->findAll();

            $room = $rooms[array_rand($rooms)];

            $startDate = (new \DateTime())->modify('+' . rand(0, 20) . ' days');
            $endDate = (clone $startDate)->modify('+' . rand(1, 10) . ' days');

            $days = $endDate->diff($startDate)->days;

            $booking = (new Booking())
                ->setCustomer($customer)
                ->setStartDate($startDate)
                ->setEndDate($endDate)
                ->setTotalPrice($days * (int) $room->getPrice())
                ->setRoom($room);


            $this->entityManager->persist($booking);
            $this->entityManager->flush();
            $this->entityManager->clear();
        }

        return Command::SUCCESS;
    }


}