<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableEntity;
use App\Message\ScrapeUrlWasCreated;
use App\Traits\EnqueueTrait;
use App\Traits\EnqueueTraitInterface;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ScrapeUrlRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class ScrapeUrl implements EnqueueTraitInterface
{
    use EnqueueTrait;
    use TimestampableEntity;

    /**
     * @var UuidInterface
     *
     * @ORM\Id
     * @ORM\Column(type="uuid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class="Ramsey\Uuid\Doctrine\UuidGenerator")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Scrape", inversedBy="urls")
     */
    private $scrape;

    /**
     * @ORM\Column(type="boolean")
     */
    private $indexed;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $statusCode;

    public function __construct(Scrape $scrape, string $url)
    {
        $this->indexed = false;
        $this->scrape = $scrape;
        $this->url = $url;
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @return Scrape
     */
    public function getScrape(): Scrape
    {
        return $this->scrape;
    }

    /**
     * @param bool $indexed
     */
    public function setIndexed(bool $indexed): void
    {
        $this->indexed = $indexed;
    }

    /**
     * @return bool
     */
    public function isIndexed(): bool
    {
        return $this->indexed;
    }

    /**
     * @param mixed $statusCode
     */
    public function setStatusCode($statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return integer|void
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @ORM\PostPersist()
     */
    public function postPersist()
    {
        $this->recordMessage(new ScrapeUrlWasCreated($this->id));
    }
}
