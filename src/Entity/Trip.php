<?php

namespace App\Entity;

use App\Repository\TripRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TripRepository::class)
 */
class Trip
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Assert\NotBlank(
     *     message="Veuillez renseigner un nom!"
     * )
     * @Assert\Length(
     *     min=3,
     *     max=250,
     *     minMessage="{{ limit }} caractères minimum svp!",
     *     maxMessage="{{ limit }} caractères maximum svp!"
     * )
     * @ORM\Column(type="string", length=250)
     */
    private $name;

    /**
     * @Assert\NotBlank(
     *     message="Veuillez renseigner une date et heure de la sortie!"
     * )
     * @Assert\GreaterThan(
     *     value="today",
     *     message="La date de la sortie doit être ultérieure à aujourd'hui!"
     * )
     * @ORM\Column(type="datetime")
     */
    private $dateTimeStart;

    /**
     * @Assert\NotBlank(
     *     message="Veuillez renseigner une durée!"
     * )
     * @Assert\Positive(
     *     message="La durée doit être positive!"
     * )
     * @ORM\Column(type="integer")
     */
    private $duration;

    /**
     * @Assert\NotBlank(
     *     message="Veuillez renseigner une date limite d'inscription!"
     * )
     * @Assert\LessThan(
     *     propertyPath="dateTimeStart",
     *     message="La date limite d'inscription doit être antérieure à la date de la sortie"
     * )
     * @Assert\GreaterThan(
     *     value="today",
     *     message="La date limite d'inscription doit être ultérieure à aujourd'hui!"
     * )
     * @ORM\Column(type="datetime")
     */
    private $dateLimitForRegistration;

    /**
     * @Assert\NotBlank(
     *     message="Veuillez renseigner un nombre de places!"
     * )
     * @Assert\Positive(
     *     message="Le nombre de places doit être positif!"
     * )
     * @Assert\GreaterThan(
     *     value="1",
     *     message="Le nombre de places doit être supérieur à 1!"
     * )
     * @ORM\Column(type="integer")
     */
    private $maxRegistrationNumber;

    /**
     * @Assert\NotBlank(
     *     message="Veuillez renseigner une description!"
     * )
     * @Assert\Length(
     *     min=3,
     *     max=250,
     *     minMessage="{{ limit }} caractères minimum svp!",
     *     maxMessage="{{ limit }} caractères maximum svp!"
     * )
     * @ORM\Column(type="text")
     */
    private $details;

    /**
     * @ORM\ManyToOne(targetEntity=Campus::class, inversedBy="trips")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organiserCampus;

    /**
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="participatingTrips")
     */
    private $participants;

    /**
     * @ORM\ManyToOne(targetEntity=State::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $state;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="organisedTrips")
     * @ORM\JoinColumn(nullable=false)
     */
    private $organiser;

    /**
     * @Assert\NotBlank(
     *     message="Veuillez renseigner un lieu!"
     * )
     * @ORM\ManyToOne(targetEntity=Location::class, inversedBy="trips")
     * @ORM\JoinColumn(nullable=false)
     */
    private $location;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDateTimeStart(): ?\DateTimeInterface
    {
        return $this->dateTimeStart;
    }

    public function setDateTimeStart(?\DateTimeInterface $dateTimeStart): self
    {
        $this->dateTimeStart = $dateTimeStart;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getDateLimitForRegistration(): ?\DateTimeInterface
    {
        return $this->dateLimitForRegistration;
    }

    public function setDateLimitForRegistration(?\DateTimeInterface $dateLimitForRegistration): self
    {
        $this->dateLimitForRegistration = $dateLimitForRegistration;

        return $this;
    }

    public function getMaxRegistrationNumber(): ?int
    {
        return $this->maxRegistrationNumber;
    }

    public function setMaxRegistrationNumber(int $maxRegistrationNumber): self
    {
        $this->maxRegistrationNumber = $maxRegistrationNumber;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(string $details): self
    {
        $this->details = $details;

        return $this;
    }

    public function getOrganiserCampus(): ?Campus
    {
        return $this->organiserCampus;
    }

    public function setOrganiserCampus(?Campus $organiserCampus): self
    {
        $this->organiserCampus = $organiserCampus;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(User $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
        }

        return $this;
    }

    public function removeParticipant(User $participant): self
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getOrganiser(): ?User
    {
        return $this->organiser;
    }

    public function setOrganiser(?User $organiser): self
    {
        $this->organiser = $organiser;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

}
