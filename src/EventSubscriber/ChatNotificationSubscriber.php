<?php

namespace App\EventSubscriber;

use App\Event\ChatMessageCreatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class ChatNotificationSubscriber implements EventSubscriberInterface
{
    // Підключаємо Mercure Hub
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

        $payload = json_encode([
            'id' => $messageEntity->getId(),
            'content' => $messageEntity->getContent(),
            'sender' => $messageEntity->getRelation()->getName(),
        ]);

        $topic = 'http://mysite.com/chat';

        $update = new Update(
            $topic,
            $payload
        );

        $this->hub->publish($update);
    }
}