<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Form\ContactsFormType;
use App\Form\MessageType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ContactsController extends AbstractController
{
    #[Route('/contacts', name: 'app_contacts')]
    public function index(EntityManagerInterface $em, Request $request): Response
    {
        $message = new Message();

        $admin = $em->createQueryBuilder()
            ->select('users')
            ->from(User::class, 'users')
            ->where('users.roles LIKE :role')
            ->setParameter('role', '%ADMIN%')
            ->getQuery()
            ->getOneOrNullResult();



        $form = $this->createForm(ContactsFormType::class, $message);

        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $message = $form->getData();
            $message->setSender($this->getUser());
            $message->setRecipient($admin);

            $em->persist($message);
            $em->flush();
            $this->addFlash('success', 'ur msg was sent');
            return $this->redirectToRoute('app_contacts');
        }

        return $this->render('contacts.html.twig', ['form' => $form]);
    }
}