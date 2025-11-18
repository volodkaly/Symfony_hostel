<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);



        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $email = $data->getEmail();

            // Перевірка чи існує вже користувач
            $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingUser) {
                $this->addFlash('error', 'Цей email вже зареєстрований!');
                return $this->redirectToRoute('app_register');
            }

            $user->setPassword(
                $passwordHasher->hashPassword($user, $user->getPassword())
            );
            $user->setRoles($user->getRoles());

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('app_room');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

}
