<?php

namespace App\Controller;

use App\Entity\Room;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class RoomController extends AbstractController
{
    #[Route('/rooms', name: 'app_room')]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $maxPrice = $request->query->getInt('maxPrice', 1000);
        $minRating = $request->query->getInt('minRating', 1);
        $minCapacity = $request->query->getInt('minCapacity', 1);
        $start = $request->query->get('start');
        $end = $request->query->get('end');

        $qb = $em->createQueryBuilder()
            ->select('r', 'AVG(rev.mark) as average_rating')
            ->from(Room::class, 'r')
            ->where('r.price <= :maxPrice')
            ->setParameter('maxPrice', $maxPrice)
            ->andWhere('r.capacity >= :minCapacity')
            ->setParameter('minCapacity', $minCapacity)
            ->leftJoin('r.bookings', 'b')
            ->leftJoin('b.review', 'rev')

            ->groupBy('r.id')
            ->having('average_rating >= :minRating OR average_rating IS NULL')
            ->setParameter('minRating', $minRating);

        if ($start && $end) {
            $start = new \DateTime($start);
            $end = new \DateTime($end);

            $qb->leftJoin(
                'r.bookings',
                'b_occupied',
                'WITH',
                'b_occupied.start_date < :end AND b_occupied.end_date > :start'
            );
            $qb->andWhere('b_occupied.id IS NULL');
            $qb->setParameter('start', $start);
            $qb->setParameter('end', $end);
        }

        $countQuery = clone $qb;
        $numberOfResults = count($countQuery->getQuery()->getArrayResult());

        $resultsWithPagination = $qb
            ->setFirstResult(10 * ($page - 1))
            ->setMaxResults(10)
            ->orderBy('r.price', 'ASC')
            ->getQuery()
            ->getArrayResult();

        $roomsWithRatings = array_map(function ($item) {
            $room = $item[0];
            $room['average_rating'] = $item['average_rating'] ? number_format((float) $item['average_rating'], 2) : 'N/A';
            return $room;
        }, $resultsWithPagination);

        return $this->render('room/index.html.twig', [
            'controller_name' => 'RoomController',
            'rooms' => $roomsWithRatings,
            'page' => $page,
            'maxPrice' => $maxPrice,
            'minRating' => $minRating,
            'minCapacity' => $minCapacity,
            'numberOfResults' => $numberOfResults,
            'start' => $start ? $start->format('Y-m-d') : (new DateTime('today'))->format('Y-m-d'),
            'end' => $end ? $end->format('Y-m-d') : (new DateTime('today'))->format('Y-m-d')
        ]);
    }
}