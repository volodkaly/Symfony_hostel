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
            // 1. Вибираємо Кімнату (r) і середнє від Відгуку (rev)
            ->select('r', 'AVG(rev.mark) as average_rating')
            // 2. Головна таблиця - Кімната. Називаємо її 'r'
            ->from(Room::class, 'r')
            // 3. Приєднуємо бронювання.
            // Беремо 'r' (кімнату), йдемо в її поле 'bookings'. Називаємо це 'b'
            ->leftJoin('r.bookings', 'b')
            // 4. Приєднуємо відгуки.
            // Беремо 'b' (бронювання), йдемо в його поле 'review' (з маленької!). Називаємо це 'rev'
            ->leftJoin('b.review', 'rev')
            // 5. Групуємо по КІМНАТІ (r.id), щоб отримати середнє для неї
            ->groupBy('r.id')
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
