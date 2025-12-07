<?php

namespace App\EventSubscriber;

use App\Event\ChatMessageCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class ChatNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(private HubInterface $hub)
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

        $payloadArr = [
            'id' => $messageEntity->getId(),
            'content' => $messageEntity->getContent(),
            'senderId' => $sender?->getId(),
            'senderName' => $sender?->getName(),
            'recipientId' => $recipient?->getId(),
        ];

        $payload = json_encode($payloadArr);

        if ($recipient) {
            $recipientTopic = 'http://mysite.com/chat/' . $recipient->getId();
            $update = new Update($recipientTopic, $payload);
            $this->hub->publish($update);
        }
    }
}