<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Repository\BookingRepository;
use App\Repository\ReviewRepository;
use App\Repository\RoomRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Room;

final class RoomController extends AbstractController
{


    #[Route('/rooms', name: 'app_room')]
    public function index(EntityManagerInterface $em, Request $request, RoomRepository $roomRepository, BookingRepository $bookingRepository, ReviewRepository $reviewRepository): Response
    {
        $page = $request->query->getInt('page', 1);

        $rooms = $em->createQueryBuilder()
            ->select('r')
            ->from(Room::class, 'r')
            ->setFirstResult($page * 10 - 10)
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult();

        foreach ($rooms as &$room) {
            $roomMarks = [];
            $bookings = $bookingRepository->findBy(['room' => $room['id']]);
            foreach ($bookings as $booking) {
                $foundReviews = $reviewRepository->findBy(['Booking' => $booking->getId()]);
                foreach ($foundReviews as $review) {
                    $roomMarks[] = $review->getMark();
                }
            }
            if (count($roomMarks) > 0) {
                $avg = array_sum($roomMarks) / count($roomMarks);
                $room['average_rating'] = number_format($avg, 2);
            } else {
                $room['average_rating'] = 'no rating yet';
            }
        }
        unset($room);

        return $this->render('room/index.html.twig', [
            'controller_name' => 'RoomController',
            'rooms' => $rooms,
            'page' => $page,

        ]);
    }
}
