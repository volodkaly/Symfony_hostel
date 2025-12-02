<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Review;
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

        $results = $em->createQueryBuilder()
            ->select('r', 'AVG(rev.mark) as average_rating')
            ->from(Room::class, 'r')
            ->leftJoin('r.bookings', 'b')
            ->leftJoin('b.review', 'rev')
            ->groupBy('r.id')
            ->setFirstResult($page * 10 - 10)
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult();

        //array structure transformation
        $roomsWithRatings = array_map(function ($item) {
            $room = $item[0];
            $room['average_rating'] = $item['average_rating'] ? number_format($item['average_rating'], 2) : 'N/A';
            return $room;
        }, $results);

        return $this->render('room/index.html.twig', [
            'controller_name' => 'RoomController',
            'rooms' => $roomsWithRatings,
            'page' => $page,

        ]);
    }
}
