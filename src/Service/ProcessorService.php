<?php

namespace App\Service;

use App\Traits\EnqueueTraitInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class ProcessorService
{
    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function __construct(MessageBusInterface $messageBus)
    {
        $this->messageBus = $messageBus;
    }

    /**
     * @param EnqueueTraitInterface $enqueueTrait
     */
    public function handle(EnqueueTraitInterface $enqueueTrait)
    {
        foreach ($enqueueTrait->getRecordedMessages() as $message) {
            $this->messageBus->dispatch($message);
        }
    }
}
