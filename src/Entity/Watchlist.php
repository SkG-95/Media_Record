<?php

namespace App\Entity;

use App\Repository\WatchlistRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: WatchlistRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Watchlist
{
    public const STATUS_TO_WATCH = 'to_watch';
    public const STATUS_WATCHING = 'watching';
    public const STATUS_WATCHED = 'watched';
    public const STATUS_DROPPED = 'dropped';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'watchlists')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'watchlists')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Media $media = null;

    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: [
        self::STATUS_TO_WATCH,
        self::STATUS_WATCHING,
        self::STATUS_WATCHED,
        self::STATUS_DROPPED
    ])]
    private ?string $status = self::STATUS_TO_WATCH;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->status = self::STATUS_TO_WATCH;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getMedia(): ?Media
    {
        return $this->media;
    }

    public function setMedia(?Media $media): static
    {
        $this->media = $media;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        if (!in_array($status, [
            self::STATUS_TO_WATCH,
            self::STATUS_WATCHING,
            self::STATUS_WATCHED,
            self::STATUS_DROPPED
        ])) {
            throw new \InvalidArgumentException("Statut invalide");
        }
        
        $this->status = $status;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(?\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->created_at = new \DateTime();
    }

    // Méthodes utilitaires
    public function isToWatch(): bool
    {
        return $this->status === self::STATUS_TO_WATCH;
    }

    public function isWatching(): bool
    {
        return $this->status === self::STATUS_WATCHING;
    }

    public function isWatched(): bool
    {
        return $this->status === self::STATUS_WATCHED;
    }

    public function isDropped(): bool
    {
        return $this->status === self::STATUS_DROPPED;
    }
}
