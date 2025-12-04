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

        // 1. Формуємо дані для відправки (JSON)
        $payload = json_encode([
            'id' => $messageEntity->getId(),
            'content' => $messageEntity->getContent(),
            // Тут ми беремо ID юзера. Якщо в Entity User є метод getEmail(), можна його
            'sender' => $messageEntity->getRelation() ? $messageEntity->getRelation()->getId() : 'Anonim',
        ]);

        // 2. Створюємо оновлення.
        // ВАЖЛИВО: Ця адреса (Topic) має співпадати з тією, що в JavaScript
        $topic = 'http://mysite.com/chat';

        $update = new Update(
            $topic,
            $payload
        );

        // 3. Відправляємо в хаб!
        $this->hub->publish($update);
    }
}