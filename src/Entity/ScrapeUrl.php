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
     * @var string
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

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $title;
    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ogType;
    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ogUrl;
    /**
     * @var string|null
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ogTitle;
    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $ogDescription;
    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $ogImage;
    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;
    /**
     * @var string|null
     * @ORM\Column(type="text", nullable=true)
     */
    private $canonical;
    /**
     * @var ScrapeUrl|null
     * @ORM\ManyToOne(targetEntity="ScrapeUrl")
     */
    private $foundOn;

    public function __construct(Scrape $scrape, string $url, ScrapeUrl $foundOn = null)
    {
        $this->indexed = false;
        $this->scrape = $scrape;
        $this->url = $url;
        if ($foundOn) {
            $this->foundOn = $foundOn;
        }
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

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getOgType(): ?string
    {
        return $this->ogType;
    }

    /**
     * @param string|null $ogType
     */
    public function setOgType(?string $ogType): void
    {
        $this->ogType = $ogType;
    }

    /**
     * @return string|null
     */
    public function getOgUrl(): ?string
    {
        return $this->ogUrl;
    }

    /**
     * @param string|null $ogUrl
     */
    public function setOgUrl(?string $ogUrl): void
    {
        $this->ogUrl = $ogUrl;
    }

    /**
     * @return string|null
     */
    public function getOgTitle(): ?string
    {
        return $this->ogTitle;
    }

    /**
     * @param string|null $ogTitle
     */
    public function setOgTitle(?string $ogTitle): void
    {
        $this->ogTitle = $ogTitle;
    }

    /**
     * @return string|null
     */
    public function getOgDescription(): ?string
    {
        return $this->ogDescription;
    }

    /**
     * @param string|null $ogDescription
     */
    public function setOgDescription(?string $ogDescription): void
    {
        $this->ogDescription = $ogDescription;
    }

    /**
     * @return string|null
     */
    public function getOgImage(): ?string
    {
        return $this->ogImage;
    }

    /**
     * @param string|null $ogImage
     */
    public function setOgImage(?string $ogImage): void
    {
        $this->ogImage = $ogImage;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return string|null
     */
    public function getCanonical(): ?string
    {
        return $this->canonical;
    }

    /**
     * @param string|null $canonical
     */
    public function setCanonical(?string $canonical): void
    {
        $this->canonical = $canonical;
    }

    /**
     * @return ScrapeUrl|null
     */
    public function getFoundOn(): ?ScrapeUrl
    {
        return $this->foundOn;
    }
}
