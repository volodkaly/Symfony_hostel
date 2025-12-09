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
    #[Route('/rooms', name: 'app_room')] public function index(EntityManagerInterface $em, Request $request, RoomRepository $roomRepository, BookingRepository $bookingRepository, ReviewRepository $reviewRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $maxPrice = $request->query->getInt('maxPrice', 1000);
        $minRating = $request->query->getInt('minRating', 1);
        $minCapacity = $request->query->getInt('minCapacity', 1);

        $results = $em->createQueryBuilder()
            ->select('r', 'AVG(rev.mark) as average_rating')
            ->from(Room::class, 'r')
            ->where('r.price <= :maxPrice')
            ->setParameter('maxPrice', $maxPrice)
            ->andWhere('r.capacity >= :minCapacity')
            ->setParameter('minCapacity', $minCapacity)
            ->having('average_rating >= :minRating OR average_rating IS NULL')
            ->setParameter('minRating', $minRating)
            ->leftJoin('r.bookings', 'b')
            ->leftJoin('b.review', 'rev')
            ->groupBy('r.id');

        $results2 = clone $results;
        $numberOfResults = count($results2->getQuery()->getArrayResult());

        $resultsWithPagination = $results
            ->setFirstResult(10 * $page - 10)
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult();

        //array structure transformation 
        $roomsWithRatings = array_map(function ($item) {
            $room = $item[0];
            $room['average_rating'] = $item['average_rating'] ? number_format($item['average_rating'], 2) : 'N/A';
            return $room;
        }, $resultsWithPagination);


        return $this->render(
            'room/index.html.twig',
            [
                'controller_name' => 'RoomController',
                'rooms' => $roomsWithRatings,
                'page' => $page,
                'maxPrice' => $maxPrice,
                'minRating' => $minRating,
                'minCapacity' => $minCapacity,
                'numberOfResults' => $numberOfResults,
            ]
        );
    }
}
