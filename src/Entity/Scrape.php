<?php

namespace App\Entity;

use App\Entity\Traits\TimestampableEntity;
use App\Message\ScrapeWasCreated;
use App\Traits\EnqueueTrait;
use App\Traits\EnqueueTraitInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ScrapeRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Scrape implements EnqueueTraitInterface
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
     * @var ScrapeUrl[]|Collection
     * @ORM\OneToMany(targetEntity="ScrapeUrl", mappedBy="scrape")
     */
    private $urls;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $url;

    public function __construct()
    {
        $this->urls = new ArrayCollection();
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return ScrapeUrl[]|Collection
     */
    public function getUrls()
    {
        return $this->urls;
    }

    /**
     * @ORM\PostPersist()
     */
    public function postPersist()
    {
        $this->recordMessage(new ScrapeWasCreated($this->id));
    }

    public function getIndexed()
    {
        $result = [];
        foreach ($this->urls as $url) {
            $indexed = $url->isIndexed();
            if (false === isset($result[$indexed])) {
                $result[$indexed] = 0;
            }
            $result[$indexed]++;
        }

        return [
            'data' => array_values($result),
            'labels' => array_keys($result),
        ];
    }

    public function getStatusCodes()
    {
        $result = [];
        foreach ($this->urls as $url) {
            $code = $url->getStatusCode();
            if (false === isset($result[$code])) {
                $result[$code] = 0;
            }
            $result[$code]++;
        }

        return [
            'data' => array_values($result),
            'labels' => array_keys($result),
        ];
        }
}
