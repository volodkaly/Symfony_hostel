<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatPageController extends AbstractController
{
    #[Route('/chat-test', name: 'app_chat_test')]
    public function index(): Response
    {
        return $this->render('chat/index.html.twig');
    }
}