<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"username"})
 * @ORM\Table(name="app_user")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner votre prénom svp !")
     * @Assert\Length(min="2", max="50", minMessage="Le pseudo doit contenir au moins {{ limit }} caractères !", maxMessage="Le pseudo ne doit pas dépasser {{ limit }} caractères !")
     * @ORM\Column(type="string", length=50, nullable=true, unique=true)
     */
    private $username;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner votre nom svp !")
     * @Assert\Length(min="2", max="50", minMessage="Le nom doit contenir au moins {{ limit }} caractères !", maxMessage="Le nom ne doit pas dépasser {{ limit }} caractères !")
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $nom;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner votre prénom svp !")
     * @Assert\Length(min="2", max="50", minMessage="Le prénom doit contenir au moins {{ limit }} caractères !", maxMessage="Le prénom ne doit pas dépasser {{ limit }} caractères !")
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $prenom;

    /**
     * @Assert\NotBlank(message="Veuillez renseigner votre téléphone svp !")
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $telephone;
/* * @Assert\Regex(pattern="/^\+33\(0\)[0-9]*$/", message="Le numéro de téléphone n'est pas valide")*/

    /**
     * @Assert\NotBlank(message="Veuillez renseigner votre adresse email svp !")
     * @Assert\Email(message = "L'adresse email {{ value }} n'est pas valide.", checkMX = true)
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $administrateur;

    /**
     * @ORM\Column(type="boolean")
     */
    private $actif;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Site", inversedBy="users")
     */
    private $site;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Sortie", mappedBy="organisateur")
     */
    private $sortiesOrganise;

    /**
     * @Assert\Length(min="2", max="50", minMessage="Le mot de passe doit contenir au moins {{ limit }} caractères !", maxMessage="Le mot de passe ne doit pas dépasser {{ limit }} caractères !")
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $password;

    /**
     *
     */
    private $roles;

    public function getRoles()
    {
        if ($this->getAdministrateur()){
            return ["ROLE_USER", "ROLE_ADMIN"];
        } else {
            return ["ROLE_USER"];
        }

    }

    public function setRoles(string $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSortiesOrganise()
    {
        return $this->sortiesOrganise;
    }

    /**
     * @param mixed $sortiesOrganise
     * @return User
     */
    public function setSortiesOrganise($sortiesOrganise)
    {
        $this->sortiesOrganise = $sortiesOrganise;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param mixed $site
     * @return User
     */
    public function setSite($site)
    {
        $this->site = $site;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAdministrateur(): ?bool
    {
        return $this->administrateur;
    }

    public function setAdministrateur(bool $administrateur): self
    {
        $this->administrateur = $administrateur;

        return $this;
    }

    public function getActif(): ?bool
    {
        return $this->actif;
    }

    public function setActif(bool $actif): self
    {
        $this->actif = $actif;

        return $this;
    }
}
