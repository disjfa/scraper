<?php

namespace App\Traits;

use App\Message\Message;

trait EnqueueTrait
{
    /**
     * @var array
     */
    private $messages = [];

    /**
     * @param Message $message
     */
    public function recordMessage(Message $message)
    {
        $this->messages[] = $message;
    }

    /**
     * @return Message[]
     */
    public function getRecordedMessages()
    {
        $messages = $this->messages;
        $this->messages = [];

        return $messages;
    }
}
