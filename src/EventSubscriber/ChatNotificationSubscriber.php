<?php

namespace App\EventSubscriber;

use App\Event\ChatMessageCreatedEvent;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class ChatNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(private HubInterface $hub, private UserRepository $userRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ChatMessageCreatedEvent::NAME => 'onMessageSent',
        ];
    }

    public function onMessageSent(ChatMessageCreatedEvent $event): void
    {
        $messageEntity = $event->getMessage();
        $sender = $messageEntity->getSender();
        $recipient = $messageEntity->getRecipient();

        if (!$recipient) {
            return;
        }

        // --- 1. DETERMINE SENDER NAME ---
        // We trust the DB name because your screenshot shows they exist.
        $senderName = $sender?->getName();

        // Fallback: If DB name is missing, use "Admin" or Email
        if (empty($senderName) && $sender) {
            $roles = $sender->getRoles();
            $senderName = in_array('ROLE_ADMIN', $roles) ? 'Admin' : $sender->getUserIdentifier();
        }

        // --- 2. BUILD JSON PAYLOAD ---
        $payloadArr = [
            'id' => $messageEntity->getId(),
            'content' => $messageEntity->getContent(),
            'senderId' => $sender?->getId(),
            'senderName' => $senderName,
            'sender' => $senderName, // Keep this for compatibility with JS
            'recipientId' => $recipient->getId(),
        ];

        $payload = json_encode($payloadArr);

        // --- 3. SEND TO MERCURE ---

        // A. Check if Recipient is an Admin
        $recipientRoles = $recipient->getRoles();
        if (in_array('ROLE_ADMIN', $recipientRoles, true)) {
            // Notify all admins
            $admins = $this->userRepository->createQueryBuilder('u')
                ->where("u.roles LIKE :role")
                ->setParameter('role', '%ROLE_ADMIN%')
                ->getQuery()
                ->getResult();

            foreach ($admins as $admin) {
                // Topic: http://mysite.com/chat/1
                $this->hub->publish(new Update('http://mysite.com/chat/' . $admin->getId(), $payload));
            }
        }
        // B. Recipient is a Normal User (User 38, ID 2)
        else {
            // Topic: http://mysite.com/chat/2
            $this->hub->publish(new Update('http://mysite.com/chat/' . $recipient->getId(), $payload));
        }
    }
}