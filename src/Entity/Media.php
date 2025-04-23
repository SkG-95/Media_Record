<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Media
{
    public const TYPE_MOVIE = 'movie';
    public const TYPE_TV = 'tv';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $tmdb_id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 10)]
    #[Assert\Choice(choices: [self::TYPE_MOVIE, self::TYPE_TV])]
    private ?string $media_type = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $release_date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $poster_path = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $overview = null;

    #[ORM\Column(nullable: true)]
    private ?float $vote_average = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $last_updated = null;

    #[ORM\OneToMany(mappedBy: 'media', targetEntity: Favorite::class, orphanRemoval: true)]
    private Collection $favorites;

    #[ORM\OneToMany(mappedBy: 'media', targetEntity: Review::class, orphanRemoval: true)]
    private Collection $reviews;

    #[ORM\OneToMany(mappedBy: 'media', targetEntity: Watchlist::class, orphanRemoval: true)]
    private Collection $watchlists;

    #[ORM\OneToMany(mappedBy: 'media', targetEntity: MediaGenre::class, orphanRemoval: true)]
    private Collection $mediaGenres;

    public function __construct()
    {
        $this->last_updated = new \DateTime();
        $this->favorites = new ArrayCollection();
        $this->reviews = new ArrayCollection();
        $this->watchlists = new ArrayCollection();
        $this->mediaGenres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTmdbId(): ?int
    {
        return $this->tmdb_id;
    }

    public function setTmdbId(?int $tmdb_id): static
    {
        $this->tmdb_id = $tmdb_id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getMediaType(): ?string
    {
        return $this->media_type;
    }

    public function setMediaType(?string $media_type): static
    {
        if ($media_type !== null && !in_array($media_type, [self::TYPE_MOVIE, self::TYPE_TV])) {
            throw new \InvalidArgumentException("Type de média invalide");
        }
        
        $this->media_type = $media_type;

        return $this;
    }

    public function isMovie(): bool
    {
        return $this->media_type === self::TYPE_MOVIE;
    }

    public function isTvShow(): bool
    {
        return $this->media_type === self::TYPE_TV;
    }

    public function getReleaseDate(): ?\DateTimeInterface
    {
        return $this->release_date;
    }

    public function setReleaseDate(?\DateTimeInterface $release_date): static
    {
        $this->release_date = $release_date;

        return $this;
    }

    public function getReleaseDateFormatted(string $format = 'd/m/Y'): ?string
    {
        return $this->release_date ? $this->release_date->format($format) : null;
    }

    public function getReleaseYear(): ?int
    {
        return $this->release_date ? (int)$this->release_date->format('Y') : null;
    }

    public function getPosterPath(): ?string
    {
        return $this->poster_path;
    }

    public function setPosterPath(?string $poster_path): static
    {
        $this->poster_path = $poster_path;

        return $this;
    }

    public function getPosterUrl(string $size = 'w500'): ?string
    {
        if (!$this->poster_path) {
            return null;
        }
        
        return 'https://image.tmdb.org/t/p/' . $size . $this->poster_path;
    }

    public function getOverview(): ?string
    {
        return $this->overview;
    }

    public function setOverview(?string $overview): static
    {
        $this->overview = $overview;

        return $this;
    }

    public function getShortOverview(int $length = 150): ?string
    {
        if (!$this->overview) {
            return null;
        }
        
        if (strlen($this->overview) <= $length) {
            return $this->overview;
        }
        
        return substr($this->overview, 0, $length) . '...';
    }

    public function getVoteAverage(): ?float
    {
        return $this->vote_average;
    }

    public function setVoteAverage(?float $vote_average): static
    {
        $this->vote_average = $vote_average;

        return $this;
    }

    public function getVoteAverageFormatted(int $decimals = 1): ?string
    {
        return $this->vote_average !== null ? number_format($this->vote_average, $decimals) : null;
    }

    public function getLastUpdated(): ?\DateTimeInterface
    {
        return $this->last_updated;
    }

    public function setLastUpdated(?\DateTimeInterface $last_updated): static
    {
        $this->last_updated = $last_updated;

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateLastUpdated(): void
    {
        $this->last_updated = new \DateTime();
    }

    /**
     * @return Collection<int, Favorite>
     */
    public function getFavorites(): Collection
    {
        return $this->favorites;
    }

    public function addFavorite(Favorite $favorite): static
    {
        if (!$this->favorites->contains($favorite)) {
            $this->favorites->add($favorite);
            $favorite->setMedia($this);
        }

        return $this;
    }

    public function removeFavorite(Favorite $favorite): static
    {
        if ($this->favorites->removeElement($favorite)) {
            // set the owning side to null (unless already changed)
            if ($favorite->getMedia() === $this) {
                $favorite->setMedia(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Review>
     */
    public function getReviews(): Collection
    {
        return $this->reviews;
    }

    public function addReview(Review $review): static
    {
        if (!$this->reviews->contains($review)) {
            $this->reviews->add($review);
            $review->setMedia($this);
        }

        return $this;
    }

    public function removeReview(Review $review): static
    {
        if ($this->reviews->removeElement($review)) {
            // set the owning side to null (unless already changed)
            if ($review->getMedia() === $this) {
                $review->setMedia(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Watchlist>
     */
    public function getWatchlists(): Collection
    {
        return $this->watchlists;
    }

    public function addWatchlist(Watchlist $watchlist): static
    {
        if (!$this->watchlists->contains($watchlist)) {
            $this->watchlists->add($watchlist);
            $watchlist->setMedia($this);
        }

        return $this;
    }

    public function removeWatchlist(Watchlist $watchlist): static
    {
        if ($this->watchlists->removeElement($watchlist)) {
            // set the owning side to null (unless already changed)
            if ($watchlist->getMedia() === $this) {
                $watchlist->setMedia(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, MediaGenre>
     */
    public function getMediaGenres(): Collection
    {
        return $this->mediaGenres;
    }

    public function addMediaGenre(MediaGenre $mediaGenre): static
    {
        if (!$this->mediaGenres->contains($mediaGenre)) {
            $this->mediaGenres->add($mediaGenre);
            $mediaGenre->setMedia($this);
        }

        return $this;
    }

    public function removeMediaGenre(MediaGenre $mediaGenre): static
    {
        if ($this->mediaGenres->removeElement($mediaGenre)) {
            // set the owning side to null (unless already changed)
            if ($mediaGenre->getMedia() === $this) {
                $mediaGenre->setMedia(null);
            }
        }

        return $this;
    }

    /**
     * Get all genres associated with this media
     * 
     * @return Collection<int, Genre>
     */
    public function getGenres(): Collection
    {
        $genres = new ArrayCollection();
        
        foreach ($this->mediaGenres as $mediaGenre) {
            $genres->add($mediaGenre->getGenre());
        }
        
        return $genres;
    }

    /**
     * Get the average rating from user reviews
     */
    public function getUserRatingAverage(): ?float
    {
        if ($this->reviews->isEmpty()) {
            return null;
        }
        
        $sum = 0;
        $count = 0;
        
        foreach ($this->reviews as $review) {
            $sum += $review->getRating();
            $count++;
        }
        
        return $sum / $count;
    }

    /**
     * Check if this media is in the favorites of a specific user
     */
    public function isFavoritedByUser(User $user): bool
    {
        foreach ($this->favorites as $favorite) {
            if ($favorite->getUser() === $user) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get the watchlist entry for a specific user, if it exists
     */
    public function getWatchlistForUser(User $user): ?Watchlist
    {
        foreach ($this->watchlists as $watchlist) {
            if ($watchlist->getUser() === $user) {
                return $watchlist;
            }
        }
        
        return null;
    }
}
