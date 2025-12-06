<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\User;
use App\Event\ChatMessageCreatedEvent;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class ApiChatController extends AbstractController
{
    #[Route('/api/message/send', name: 'api_message_send', methods: ['POST'])]
    public function sendMessage(
        Request $request,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher,
        UserRepository $userRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $content = $data['content'] ?? null;

        // Отримуємо ID отримувача з форми (якщо пише адмін)
        $recipientId = $data['recipientId'] ?? null;

        if (!$content) {
            return $this->json(['error' => 'No content provided'], 400);
        }

        $currentUser = $this->getUser();

        $message = new Message();
        $message->setContent($content);
        $message->setSender($currentUser);

        // ЛОГІКА ОТРИМУВАЧА
        if ($recipientId) {
            // Якщо ID передано явно (наприклад, адмін відповідає юзеру)
            $recipient = $userRepository->find($recipientId);
            if ($recipient) {
                $message->setRecipient($recipient);
            }
        } elseif (in_array('ROLE_ADMIN', $currentUser->getRoles())) {
            // Якщо адмін пише без отримувача - це помилка (або загальний чат)
            // Тут можна додати логіку
        } else {
            // Якщо пише звичайний юзер - отримувач не обов'язковий (це тікет на підтримку)
            // Або можна знайти першого адміна і призначити йому
        }

        // Зберігаємо в БД
        $entityManager->persist($message);
        $entityManager->flush();

        // Відправляємо в Mercure (івент)
        $event = new ChatMessageCreatedEvent($message);
        $dispatcher->dispatch($event, ChatMessageCreatedEvent::NAME);

        return $this->json([
            'status' => 'success',
            'id' => $message->getId(),
            'sender' => $currentUser->getUserIdentifier() // або ->getId()
        ]);
    }
}