<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"email"}, message="Cet email est déjà pris!")
 * @UniqueEntity(fields={"username"}, message="Ce pseudo est déjà pris!")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(
     *     message="Veuillez renseigner votre pseudo!"
     * )
     * @Assert\Length(
     *     min=3,
     *     max=50,
     *     minMessage="{{ limit }} caractères minimum svp!",
     *     maxMessage="{{ limit }} caractères maximum svp!"
     * )
     * @Assert\Regex(
     *     pattern="/^[\w]*$/",
     *     message="Seuls les caractères alphanumériques et _ sont acceptés!"
     * )
     * @ORM\Column(type="string", length=50, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @Assert\NotBlank(
     *     message="Veuillez renseigner votre nom!"
     * )
     * @Assert\Length(
     *     min=3,
     *     max=250,
     *     minMessage="{{ limit }} caractères minimum svp!",
     *     maxMessage="{{ limit }} caractères maximum svp!"
     * )
     * @ORM\Column(type="string", length=250)
     */
    private $last_name;

    /**
     * @Assert\NotBlank(
     *     message="Veuillez renseigner votre prénom!"
     * )
     * @Assert\Length(
     *     min=3,
     *     max=250,
     *     minMessage="{{ limit }} caractères minimum svp!",
     *     maxMessage="{{ limit }} caractères maximum svp!"
     * )
     * @ORM\Column(type="string", length=250)
     */
    private $first_name;

    /**
     * @Assert\NotBlank(
     *     message="Veuillez renseigner votre email!"
     * )
     * @Assert\Length(
     *     min=6,
     *     max=250,
     *     minMessage="{{ limit }} caractères minimum svp!",
     *     maxMessage="{{ limit }} caractères maximum svp!"
     * )
     * @ORM\Column(type="string", length=250)
     */
    private $email;

    /**
     * @Assert\NotBlank(
     *     message="Veuillez renseigner votre téléphone!"
     * )
     * @Assert\Regex(
     *     pattern="/^(0|(\\+33)|(0033))[1-9][0-9]{8}$/",
     *     message="Votre numéro de téléphone n'est pas valide!"
     * )
     * @ORM\Column(type="string", length=15)
     */
    private $phone;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_created;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $date_updated;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class, inversedBy="users")
     * @ORM\JoinColumn(nullable=false)
     */
    private $campus;

    /**
     * @ORM\ManyToMany(targetEntity=Trip::class, mappedBy="participants")
     */
    private $participatingTrips;

    /**
     * @ORM\OneToMany(targetEntity=Trip::class, mappedBy="organiser")
     */
    private $organisedTrips;

    /**
     * @ORM\Column(type="string", length=250, nullable=true)
     */
    private $pictureFileName;

    public function __construct()
    {

        $this->participatingTrips = new ArrayCollection();
        $this->organisedTrips = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(?string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(?string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->date_created;
    }

    public function setDateCreated(\DateTimeInterface $date_created): self
    {
        $this->date_created = $date_created;

        return $this;
    }

    public function getDateUpdated(): ?\DateTimeInterface
    {
        return $this->date_updated;
    }

    public function setDateUpdated(?\DateTimeInterface $date_updated): self
    {
        $this->date_updated = $date_updated;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): self
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection|Trip[]
     */
    public function getParticipatingTrips(): Collection
    {
        return $this->participatingTrips;
    }

    public function addParticipatingTrip(Trip $participatingTrip): self
    {
        if (!$this->participatingTrips->contains($participatingTrip)) {
            $this->participatingTrips[] = $participatingTrip;
            $participatingTrip->addParticipant($this);
        }

        return $this;
    }

    public function removeParticipatingTrip(Trip $participatingTrip): self
    {
        if ($this->participatingTrips->removeElement($participatingTrip)) {
            $participatingTrip->removeParticipant($this);
        }

        return $this;
    }

    /**
     * @return Collection|Trip[]
     */
    public function getOrganisedTrips(): Collection
    {
        return $this->organisedTrips;
    }

    public function addOrganisedTrip(Trip $organisedTrip): self
    {
        if (!$this->organisedTrips->contains($organisedTrip)) {
            $this->organisedTrips[] = $organisedTrip;
            $organisedTrip->setOrganiser($this);
        }

        return $this;
    }

    public function removeOrganisedTrip(Trip $organisedTrip): self
    {
        if ($this->organisedTrips->removeElement($organisedTrip)) {
            // set the owning side to null (unless already changed)
            if ($organisedTrip->getOrganiser() === $this) {
                $organisedTrip->setOrganiser(null);
            }
        }

        return $this;
    }

    public function getPictureFileName(): ?string
    {
        return $this->pictureFileName;
    }

    public function setPictureFileName(?string $pictureFileName): self
    {
        $this->pictureFileName = $pictureFileName;

        return $this;
    }
}
