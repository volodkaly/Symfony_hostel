<?php
namespace App\Event;

use App\Entity\Messages;
use Symfony\Contracts\EventDispatcher\Event;

class ChatMessageCreatedEvent extends Event
{
    public const NAME = 'chat.message_sent';

    public function __construct(private Messages $message)
    {
    }

    // Дозволяємо дістати об'єкт
    public function getMessage(): Messages
    {
        return $this->message;
    }
}
