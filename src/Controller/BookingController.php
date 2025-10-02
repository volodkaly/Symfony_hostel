<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Form\BookingType;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Room;

#[IsGranted('ROLE_USER')]
#[Route('/booking')]

final class BookingController extends AbstractController
{
    #[Route(name: 'app_booking_index', methods: ['GET'])]
    public function index(BookingRepository $bookingRepository): Response
    {
        return $this->render('booking/index.html.twig', [
            'bookings' => $bookingRepository->findAll(),
        ]);
    }



    #[IsGranted('ROLE_USER')]
    #[Route('/new', name: 'app_booking_new', methods: ['GET', 'POST'])]

    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $booking = new Booking();
        $chosen_room = $request->request->get('chosen_room') ?? null;
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
                $backendTotal = number_format($days * $pricePerDay, 2, '.', '');
                $booking->setTotalPrice($backendTotal);
            }

            // Get frontend total price from hidden field
            $frontendTotal = $form->get('total_price')->getData();
            if ($frontendTotal !== null && $backendTotal !== null && $frontendTotal !== $backendTotal) {
                $this->addFlash('warning', 'Total price mismatch between frontend and backend calculation. Please review your booking.');
                // Optionally, you can redirect back or handle as needed
                // return $this->redirectToRoute('app_booking_new');
            }

            $entityManager->persist($booking);
            $entityManager->flush();

            return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
        }




        return $this->render('booking/new.html.twig', [
            'booking' => $booking,
            'form' => $form,
            'chosen_room' => $chosen_room,
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
    public function edit(Request $request, Booking $booking, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('booking/edit.html.twig', [
            'booking' => $booking,
            'form' => $form,
        ]);
    }
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_booking_delete', methods: ['POST'])]
    public function delete(Request $request, Booking $booking, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $booking->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($booking);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_booking_index', [], Response::HTTP_SEE_OTHER);
    }

}
