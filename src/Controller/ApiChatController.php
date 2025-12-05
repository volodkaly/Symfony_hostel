<?php

namespace App\Controller;

use App\Entity\Messages;
use App\Entity\User;
use App\Event\ChatMessageCreatedEvent;
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
    ): JsonResponse {



        $data = json_decode($request->getContent(), true);
        $recipientId = $data['recipientId'] ?? null;
        $currentUser = $this->getUser();
        $currentUserId = $currentUser instanceof User ? $currentUser->getId() : null;

        if ($recipientId == $currentUserId) {
            if (!$data || !isset($data['content'])) {
                return $this->json(['error' => 'No content provided'], 400);
            }

            $message = new Messages();

            $message->setContent($data['content']);

            $message->setTitle('Chat Message');

            $message->setRelation($this->getUser());

            $entityManager->persist($message);
            $entityManager->flush();

            $event = new ChatMessageCreatedEvent($message);

            $dispatcher->dispatch($event, eventName: ChatMessageCreatedEvent::NAME);

            return $this->json([
                'status' => 'success',
                'id' => $message->getId(),
                'recipientId' => $recipientId,
            ]);
        } else {
            return $this->json([
                'status' => 'success',
                'result' => 'Message sent',
            ]);
        }
    }
}