<?php

namespace App\Message;

use Ramsey\Uuid\UuidInterface;

class ScrapeUrlWasCreated implements Message, AsyncMessage
{
    /**
     * @var UuidInterface
     */
    private $scrapeUrlId;

    public function __construct(UuidInterface $scrapeUrlId)
    {
        $this->scrapeUrlId = $scrapeUrlId;
    }

    /**
     * @return UuidInterface
     */
    public function getScrapeId(): UuidInterface
    {
        return $this->scrapeUrlId;
    }
}
