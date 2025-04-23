<?php

namespace App\Entity;

use App\Repository\GenreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GenreRepository::class)]
class Genre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'genre', targetEntity: MediaGenre::class, orphanRemoval: true)]
    private Collection $mediaGenres;

    public function __construct()
    {
        $this->mediaGenres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

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
            $mediaGenre->setGenre($this);
        }

        return $this;
    }

    public function removeMediaGenre(MediaGenre $mediaGenre): static
    {
        if ($this->mediaGenres->removeElement($mediaGenre)) {
            // set the owning side to null (unless already changed)
            if ($mediaGenre->getGenre() === $this) {
                $mediaGenre->setGenre(null);
            }
        }

        return $this;
    }

    /**
     * Get all media associated with this genre
     * 
     * @return Collection<int, Media>
     */
    public function getMedia(): Collection
    {
        $media = new ArrayCollection();
        
        foreach ($this->mediaGenres as $mediaGenre) {
            $media->add($mediaGenre->getMedia());
        }
        
        return $media;
    }

    /**
     * String representation of the genre
     */
    public function __toString(): string
    {
        return $this->name ?? '';
    }
}
