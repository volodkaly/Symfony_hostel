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
            ->select('r', 'AVG(rev.mark) as average_rating') // Вибираємо кімнату + середнє число
            ->from(Room::class, 'r')
            ->leftJoin('r.bookings', 'b') // Приєднуємо бронювання
            // Приєднуємо відгуки (оскільки зв'язок в Review, а не в Booking, робимо це так):
            ->leftJoin(Review::class, 'rev', 'WITH', 'rev.Booking = b')
            ->groupBy('r.id') // Групуємо, щоб рейтинг рахувався окремо для кожної кімнати
            ->setFirstResult($page * 10 - 10)
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult();

        // Трохи магії, щоб спростити структуру масиву для Twig
        // Доктрина повертає: [[0 => [...кімната...], 'average_rating' => 4.5], ...]
        // Ми робимо: [[...кімната..., 'average_rating' => 4.5], ...]
        $rooms = array_map(function ($item) {
            $room = $item[0];
            $room['average_rating'] = $item['average_rating'] ? number_format($item['average_rating'], 2) : 'N/A';
            return $room;
        }, $results);

        return $this->render('room/index.html.twig', [
            'controller_name' => 'RoomController',
            'rooms' => $rooms,
            'page' => $page,

        ]);
    }
}
