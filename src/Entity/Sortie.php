<?php

namespace App\Entity;

use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SortieRepository::class)
 */
class Sortie
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $date_sortie;

    /**
     * @ORM\Column(type="date")
     */
    private $date_fin_inscription;

    /**
     * @ORM\Column(type="integer")
     */
    private $nb_place;

    /**
     * @ORM\Column(type="integer")
     */
    private $duree;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity=Ville::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $id_ville;

    /**
     * @ORM\ManyToOne(targetEntity=Lieu::class, inversedBy="sorties")
     * @ORM\JoinColumn(nullable=false)
     */
    private $id_lieu;

    /**
     * @ORM\ManyToMany(targetEntity=Utilisateur::class, mappedBy="sorties")
     */
    private $participants;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateSortie(): ?\DateTimeInterface
    {
        return $this->date_sortie;
    }

    public function setDateSortie(\DateTimeInterface $date_sortie): self
    {
        $this->date_sortie = $date_sortie;

        return $this;
    }

    public function getDateFinInscription(): ?\DateTimeInterface
    {
        return $this->date_fin_inscription;
    }

    public function setDateFinInscription(\DateTimeInterface $date_fin_inscription): self
    {
        $this->date_fin_inscription = $date_fin_inscription;

        return $this;
    }

    public function getNbPlace(): ?int
    {
        return $this->nb_place;
    }

    public function setNbPlace(int $nb_place): self
    {
        $this->nb_place = $nb_place;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(?int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getIdVille(): ?ville
    {
        return $this->id_ville;
    }

    public function setIdVille(?ville $id_ville): self
    {
        $this->id_ville = $id_ville;

        return $this;
    }

    public function getIdLieu(): ?lieu
    {
        return $this->id_lieu;
    }

    public function setIdLieu(?lieu $id_lieu): self
    {
        $this->id_lieu = $id_lieu;

        return $this;
    }

    /**
     * @return Collection|Utilisateur[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Utilisateur $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
            $participant->addSorty($this);
        }

        return $this;
    }

    public function removeParticipant(Utilisateur $participant): self
    {
        if ($this->participants->removeElement($participant)) {
            $participant->removeSorty($this);
        }

        return $this;
    }
}
