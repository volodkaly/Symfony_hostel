<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Room;

final class RoomController extends AbstractController
{
    #[Route('/room_add_to_db', name: 'app_room_add')]
    public function addToDB(EntityManagerInterface $em): Response
    {
        $room = new Room();

        $room->setName('First Room');
        $room->setPrice(160);
        $room->setCapacity(4);

        $em->persist($room);
        $em->flush();

        $room = new Room();

        $room->setName('Second Room');
        $room->setPrice(140);
        $room->setCapacity(3);

        $em->persist($room);
        $em->flush();

        $room = new Room();

        $room->setName('Third Room');
        $room->setPrice(100);
        $room->setCapacity(2);

        $em->persist($room);
        $em->flush();

        $room = new Room();

        $room->setName('Fourth Room');
        $room->setPrice(101);
        $room->setCapacity(2);

        $em->persist($room);
        $em->flush();


        $room = new Room();

        $room->setName('Fifth Room');
        $room->setPrice(105);
        $room->setCapacity(2);

        $em->persist($room);
        $em->flush();


        $room = new Room();

        $room->setName('Sixth Room');
        $room->setPrice(108);
        $room->setCapacity(2);

        $em->persist($room);
        $em->flush();


        $room = new Room();

        $room->setName('Seventh Room');
        $room->setPrice(104);
        $room->setCapacity(2);

        $em->persist($room);
        $em->flush();


        $room = new Room();

        $room->setName('Eighth Room');
        $room->setPrice(89);
        $room->setCapacity(1);

        $em->persist($room);
        $em->flush();


        $room = new Room();

        $room->setName('Ninth Room');
        $room->setPrice(79);
        $room->setCapacity(1);

        $em->persist($room);
        $em->flush();


        $room = new Room();

        $room->setName('Tenth Room');
        $room->setPrice(87);
        $room->setCapacity(1);


        $em->persist($room);
        $em->flush();

        return $this->redirectToRoute('app_room');
    }

    #[Route('/rooms', name: 'app_room')]
    public function index(EntityManagerInterface $em): Response
    {
        $rooms = $em->createQueryBuilder()
            ->select('r')
            ->from(Room::class, 'r')
            ->getQuery()
            ->getArrayResult();



        return $this->render('room/index.html.twig', [
            'controller_name' => 'RoomController',
            'rooms' => $rooms
        ]);
    }
}
