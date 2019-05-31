<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SortieRepository")
 */
class Sortie
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateHeureDebut;

    /**
     * @ORM\Column(type="integer")
     */
    private $duree;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateLimiteInscription;

    /**
     * @ORM\Column(type="integer")
     */
    private $nbInscriptionsMax;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $infosSortie;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Etat", inversedBy="sorties")
     */
    private $etat;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Lieu", inversedBy="sorties")
     */
    private $lieu;


    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="sortiesOrganise")
     */
    private $organisateur;

    /**
     * Participants à la sortie
     * @ORM\ManyToMany(targetEntity="App\Entity\User")
     */
    private $participants;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $motifAnnulation;

    /**
     * @ORM\Column(type="boolean")
     */
    private $privee;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Groupe", inversedBy="sorties")
     */
    private $groupe;

    /**
     * @return mixed
     */
    public function getPrivee()
    {
        return $this->privee;
    }

    /**
     * @param mixed $privee
     * @return Sortie
     */
    public function setPrivee($privee)
    {
        $this->privee = $privee;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getMotifAnnulation()
    {
        return $this->motifAnnulation;
    }

    /**
     * @param mixed $motifAnnulation
     * @return Sortie
     */
    public function setMotifAnnulation($motifAnnulation)
    {
        $this->motifAnnulation = $motifAnnulation;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getOrganisateur()
    {
        return $this->organisateur;
    }

    /**
     * @param mixed $organisateur
     * @return Sortie
     */
    public function setOrganisateur($organisateur)
    {
        $this->organisateur = $organisateur;
        return $this;
    }

    /**
     * Ajouter un participant à la sortie
     * @param User $user
     */
    public function addParticipant(User $user)
    {
//        $this->participants[] = $user;
//        if (!$this->participants->contains($user)) {
            $this->participants[] = $user;
//        }

    }

    /**
     * Supprimer un participant d'une sortie
     * @param User $user
     */
    public function removeParticipant(User $user)
    {
        if($this->participants->contains($user)){
            $this->participants->removeElement($user);
        }
    }

    /**
     * @return mixed
     */
    public function getParticipants()
    {
        return $this->participants;
    }

    /**
     * @param mixed $participants
     * @return Sortie
     */
    public function setParticipants($participants)
    {
        $this->participants = $participants;
        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDateHeureDebut(): ?\DateTimeInterface
    {
        return $this->dateHeureDebut;
    }

    public function setDateHeureDebut(\DateTimeInterface $dateHeureDebut): self
    {
        $this->dateHeureDebut = $dateHeureDebut;

        return $this;
    }

    public function getDuree(): ?int
    {
        return $this->duree;
    }

    public function setDuree(int $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getDateLimiteInscription(): ?\DateTimeInterface
    {
        return $this->dateLimiteInscription;
    }

    public function setDateLimiteInscription(\DateTimeInterface $dateLimiteInscription): self
    {
        $this->dateLimiteInscription = $dateLimiteInscription;

        return $this;
    }

    public function getNbInscriptionsMax(): ?int
    {
        return $this->nbInscriptionsMax;
    }

    public function setNbInscriptionsMax(int $nbInscriptionsMax): self
    {
        $this->nbInscriptionsMax = $nbInscriptionsMax;

        return $this;
    }

    public function getInfosSortie(): ?string
    {
        return $this->infosSortie;
    }

    public function setInfosSortie(?string $infosSortie): self
    {
        $this->infosSortie = $infosSortie;

        return $this;
    }

    public function getEtat()
    {
        return $this->etat;
    }

    public function setEtat($etat): self
    {
        $this->etat = $etat;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLieu()
    {
        return $this->lieu;
    }

    /**
     * @param mixed $lieu
     * @return Sortie
     */
    public function setLieu($lieu)
    {
        $this->lieu = $lieu;
        return $this;
    }

    public function getGroupe(): ?Groupe
    {
        return $this->groupe;
    }

    public function setGroupe(?Groupe $groupe): self
    {
        $this->groupe = $groupe;

        return $this;
    }

}
