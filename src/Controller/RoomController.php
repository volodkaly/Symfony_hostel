<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Room;

final class RoomController extends AbstractController
{


    #[Route('/rooms', name: 'app_room')]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $rooms = $em->createQueryBuilder()
            ->select('r')
            ->from(Room::class, 'r')
            ->setFirstResult($page * 10 - 10)
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult();



        return $this->render('room/index.html.twig', [
            'controller_name' => 'RoomController',
            'rooms' => $rooms,
            'page' => $page,
        ]);
    }
}
