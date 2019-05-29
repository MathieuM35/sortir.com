<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields={"username"})
 * @ORM\Table(name="app_user")
 */
class User implements AdvancedUserInterface
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
     * @Assert\Regex(pattern="/^\+33\(0\)[0-9]*$/", message="Le numéro de téléphone n'est pas valide")
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $telephone;
/* */

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
     * @ORM\OneToMany(targetEntity="App\Entity\Sortie", mappedBy="organisateur",cascade={"remove"})
     */
    private $sortiesOrganise;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Groupe", mappedBy="createur")
     */
    private $groupeCreateur;

    /**
     * @return ArrayCollection
     */
    public function getGroupes(): ArrayCollection
    {
        return $this->groupes;
    }

    /**
     * @param ArrayCollection $groupes
     * @return User
     */
    public function setGroupes(ArrayCollection $groupes): User
    {
        $this->groupes = $groupes;
        return $this;
    }


    /**
     * @Assert\Length(min="2", max="255", minMessage="Le mot de passe doit contenir au moins {{ limit }} caractères !", maxMessage="Le mot de passe ne doit pas dépasser {{ limit }} caractères !")
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $password;

    /**
     *
     */
    private $roles;

    /**
     * @ORM\Column(type="string", nullable=true)
     *
     * @Assert\File(mimeTypes={ "image/png","image/jpeg","image/jpg","image/gif" }, groups={"update"})
     */
    private $photo;



    public function __construct()
    {
        $this->groupes = new ArrayCollection();
        $this->groupeCreateur = new ArrayCollection();
    }

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     * @return User
     */
    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    public function setResetToken($token){
        $this->token = $token;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param mixed $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

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

//    /**
//     * @see \Serializable::serialize()
//     */
//    public function serialize()
//    {
//        return serialize(array(
//            $this->id,
//            $this->photo,
//        ));
//    }
//
//    /**
//     * @see \Serializable::unserialize()
//     */
//    public function unserialize($serialized)
//    {
//        list(
//            $this->id,
//            $this->photo,
//            ) = unserialize($serialized, array('allowed_classes' => false));
//    }

    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        return $this->actif;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return $this->actif;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        return $this->actif;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return $this->actif;
    }

    /**
     * @return Collection|Groupe[]
     */
    public function getGroupeCreateur(): Collection
    {
        return $this->groupeCreateur;
    }

    public function addGroupeCreateur(Groupe $groupeCreateur): self
    {
        if (!$this->groupeCreateur->contains($groupeCreateur)) {
            $this->groupeCreateur[] = $groupeCreateur;
            $groupeCreateur->setCreateur($this);
        }

        return $this;
    }

    public function removeGroupeCreateur(Groupe $groupeCreateur): self
    {
        if ($this->groupeCreateur->contains($groupeCreateur)) {
            $this->groupeCreateur->removeElement($groupeCreateur);
            // set the owning side to null (unless already changed)
            if ($groupeCreateur->getCreateur() === $this) {
                $groupeCreateur->setCreateur(null);
            }
        }

        return $this;
    }


}
