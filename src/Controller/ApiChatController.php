<?php

namespace App\Controller;

use App\Entity\Message;
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

        $recipientId = $data['recipientId'] ?? null;

        if (!$content) {
            return $this->json(['error' => 'No content provided'], 400);
        }

        $currentUser = $this->getUser();

        $message = new Message();
        $message->setContent($content);
        $message->setSender($currentUser);

        if ($recipientId) {

            $recipient = $userRepository->find($recipientId);
            if ($recipient) {
                $message->setRecipient($recipient);
            }
        } elseif (in_array('ROLE_ADMIN', $currentUser->getRoles())) {

        } else {

            $admins = $userRepository->createQueryBuilder('u')
                ->where("u.roles LIKE :role")
                ->setParameter('role', '%ROLE_ADMIN%')
                ->setMaxResults(1)
                ->getQuery()
                ->getResult();
            $admin = $admins[0] ?? null;

            if ($admin) {
                $message->setRecipient($admin);
            }
        }

        $entityManager->persist($message);
        $entityManager->flush();

        $event = new ChatMessageCreatedEvent($message);
        $dispatcher->dispatch($event, ChatMessageCreatedEvent::NAME);

        return $this->json([
            'status' => 'success',
            'id' => $message->getId(),
            'sender' => $currentUser->getUserIdentifier(),
            'recipientId' => $message->getRecipient()?->getId()
        ]);
    }
}