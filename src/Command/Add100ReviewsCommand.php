<?php

namespace App\Command;

use App\Entity\Review;
use App\Repository\BookingRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ReviewRepository;
use Faker\Factory;

#[AsCommand(
    name: 'add100Reviews',
    description: 'Mocking 100 reviews',
)]
class Add100ReviewsCommand extends Command
{
    public function __construct(private EntityManagerInterface $em, private ReviewRepository $reviewRepository, private BookingRepository $bookingRepository, private LoggerInterface $logger)
    {
        parent::__construct();
    }



    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $faker = Factory::create();
        for ($i = 1; $i <= 100; $i++) {
            $bookings = $this->bookingRepository->findAll();
            $booking = $bookings[array_rand($bookings)];

            $review = new Review();
            $review->setBooking($booking);
            $review->setTitle($faker->word());
            $review->setMark(rand(1, 5));
            $review->setDescription($faker->paragraph());

            $this->em->persist($review);


        }
        $this->em->flush();
        $this->em->clear();
        $this->logger->info('custom log: 100 reviews were mocked');
        return Command::SUCCESS;
    }
}