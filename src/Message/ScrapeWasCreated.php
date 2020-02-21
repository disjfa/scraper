<?php

namespace App\Message;

use Ramsey\Uuid\UuidInterface;

class ScrapeWasCreated implements Message, AsyncMessage
{
    /**
     * @var UuidInterface
     */
    private $scrapeId;

    public function __construct(UuidInterface $scrapeId)
    {
        $this->scrapeId = $scrapeId;
    }

    /**
     * @return UuidInterface
     */
    public function getScrapeId(): UuidInterface
    {
        return $this->scrapeId;
    }
}
