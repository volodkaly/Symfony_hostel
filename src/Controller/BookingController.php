<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use App\Repository\RoomRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Room;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[IsGranted('ROLE_USER')]
#[Route('/booking')]

final class BookingController extends AbstractController
{
    #[Route(name: 'app_booking_index', methods: ['GET'])]
    public function index(BookingRepository $bookingRepository, EntityManagerInterface $em, Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $bookings = $em->createQueryBuilder()
            ->select('bookings')
            ->from(Booking::class, 'bookings')
            ->where('bookings.customer = :customer')
            ->setParameter('customer', $this->getUser())
            ->setFirstResult($page * 10 - 10)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        return $this->render('booking/index.html.twig', [
            'bookings' => $bookings,
            'page' => $page
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route(path: '/all', name: 'app_booking_index_all', methods: ['POST', 'GET'])]
    public function index_all(Request $request, BookingRepository $bookingRepository): Response
    {
        $page = $request->query->getInt('page', 1);

        $totalBookings = count($bookingRepository->findAll());
        $lastPage = (int) ceil($totalBookings / 5);



        $bookings = $bookingRepository->createQueryBuilder('b')
            ->setFirstResult($page * 5 - 5)
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        return $this->render('booking/index_all.html.twig', [
            'bookings' => $bookings,
            'page' => $page,
            'lastPage' => $lastPage,
        ]);
    }


    #[IsGranted('ROLE_USER')]
    #[Route('/new', name: 'app_booking_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, LoggerInterface $logger, RoomRepository $roomRepository, SessionInterface $session): Response
    {
        $booking = new Booking();

        if ($chosen_room = $request->request->get('chosen_room')) {
            $session->set('chosen_room', $chosen_room);
        }
        $chosen_room = $session->get('chosen_room', null);

        $price = 0;
        if ($chosen_room) {
            $chosen_room_object = $roomRepository->find($chosen_room);
            $price = $chosen_room_object->getPrice();
        }

        if ($chosen_room) {
            $booking->setRoom($entityManager->getRepository(Room::class)->find($chosen_room));
        }

        $booking->setCustomer($this->getUser());
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Calculate total price (backend)
            $start = $booking->getStartDate();
            $end = $booking->getEndDate();
            $room = $booking->getRoom();
            $backendTotal = null;

            if ($start && $end && $room) {
                $interval = $start->diff($end);
                $days = $interval->days;
                $pricePerDay = method_exists($room, 'getPrice') ? (float) $room->getPrice() : 0;
                // Форматуємо як строку, щоб збігалося з форматом бази даних
                $backendTotal = number_format($days * $pricePerDay, 2, '.', '');
                $booking->setTotalPrice($backendTotal);
            }

            $frontendTotal = $form->get('total_price')->getData();

            if ($frontendTotal !== $backendTotal) {
                $this->addFlash('warning', 'Price mismatch. Please try again.');

                return $this->render('booking/new.html.twig', [
                    'booking' => $booking,
                    'form' => $form,
                    'chosen_room' => $chosen_room,
                    'price' => $price
                ], new Response('', 422));
            }

            $entityManager->persist($booking);
            $entityManager->flush();

            $this->addFlash('success', 'Booking was created');
            $logger->info('custom log: new booking created:' . $booking->getId() . ' by user: ' . $booking->getCustomer()->getName());

            return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('booking/new.html.twig', [
            'booking' => $booking,
            'form' => $form,
            'chosen_room' => $chosen_room,
            'price' => $price
        ]);
    }


    #[IsGranted('ROLE_USER')]
    #[Route('/{id}', name: 'app_booking_show', methods: ['GET'])]
    public function show(Booking $booking): Response
    {
        return $this->render('booking/show.html.twig', [
            'booking' => $booking,
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'app_booking_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Booking $booking, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {

        $isPaid = $request->request->get('isPaid', $booking->getIsPaid());


        dump($booking->getIsPaid());
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
        }

        $entityManager->persist($booking->setIsPaid($isPaid));
        $entityManager->flush();

        $logger->info('custom log: booking ' . $booking->getId() . ' was edited by admin');

        return $this->render('booking/edit.html.twig', [
            'booking' => $booking,
            'form' => $form,
            'isPaid' => $isPaid,
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_booking_delete', methods: ['POST'])]
    public function delete(Request $request, Booking $booking, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        if ($this->isCsrfTokenValid('delete' . $booking->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($booking);
            $entityManager->flush();
        }

        $logger->info('custom log: booking ' . $booking->getId() . ' was deleted by admin');

        return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
    }

}
