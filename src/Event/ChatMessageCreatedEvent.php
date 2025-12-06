<?php
namespace App\Event;

use App\Entity\Message;
use Symfony\Contracts\EventDispatcher\Event;

class ChatMessageCreatedEvent extends Event
{
    public const NAME = 'chat.message_sent';

    public function __construct(private Message $message)
    {
    }

    // Дозволяємо дістати об'єкт
    public function getMessage(): Message
    {
        return $this->message;
    }
}
