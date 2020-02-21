<?php

namespace App\Traits;

use App\Message\Message;

interface EnqueueTraitInterface
{
    /**
     * @param Message $message
     */
    public function recordMessage(Message $message);

    /**
     * @return Message[]
     */
    public function getRecordedMessages();
}
