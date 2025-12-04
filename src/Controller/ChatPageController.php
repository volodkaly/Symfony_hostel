<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatPageController extends AbstractController
{
    // Ось цей рядок каже Symfony: "Якщо хтось йде на /chat-test, запускай цей метод"
    #[Route('/chat-test', name: 'app_chat_test')]
    public function index(): Response
    {
        // Цей метод малює файл шаблону
        return $this->render('chat/index.html.twig');
    }
}