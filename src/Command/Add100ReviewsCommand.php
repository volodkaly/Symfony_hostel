<?php

namespace App\Command;

use App\Entity\Review;
use App\Repository\BookingRepository;
use Dom\Entity;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReviewRepository;

#[AsCommand(
    name: 'add100Reviews',
    description: 'Mocks 100 reviews',
)]
class Add100ReviewsCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, private ReviewRepository $reviewRepository, private BookingRepository $bookingRepository)
    {
        parent::__construct();
    }



    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        for ($i = 1; $i <= 100; $i++) {
            $bookings = $this->bookingRepository->findAll();
            $booking = $bookings[array_rand($bookings)];

            $review = new Review();
            $review->setBooking($booking);
            $review->setTitle(str_shuffle(substr(join(range('A', 'Z')), 0, 10)));
            $review->setMark(rand(1, 5));
            $review->setDescription(str_shuffle(substr(join(range('A', 'Z')), 0, 30)));

            $this->em->persist($review);
            $this->em->flush();
            $this->em->clear();

        }

        return Command::SUCCESS;
    }
}