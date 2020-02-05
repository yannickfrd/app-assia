<?php

namespace App\Entity;

use App\Service\Phone;

use App\Form\Utils\SelectList;
use Doctrine\ORM\Mapping as ORM;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @ORM\Entity(repositoryClass="App\Repository\ServiceRepository")
 */
class Service
{
    public const SUPPORT_ACCESS = [
        1 => "Uniquement le référent du suivi",
        2 => "Tou·te·s les salarié·e·s du service",
        3 => "Tou·te·s les salarié·e·s du pôle",
        4 => "Tou·te·s les salarié·e·s de l'association"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull(message="Le nom du service ne doit pas être vide.")
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ServiceUser", mappedBy="service", orphanRemoval=true, cascade={"persist"})
     */
    private $serviceUser;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pole", inversedBy="services", cascade={"persist"})
     */
    private $pole;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SupportGroup", mappedBy="service")
     */
    private $supportGroup;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Assert\Email(message="L'adresse email n'est pas valide.")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @Assert\Regex(pattern="^0[1-9]([-._/ ]?[0-9]{2}){4}$^", match=true, message="Le numéro de téléphone est incorrect.")
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $zipCode;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $chief;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $supportAccess;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $accommodation;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $updatedBy;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ServiceDevice", mappedBy="service", orphanRemoval=true, cascade={"persist"})
     */
    private $serviceDevices;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Accommodation", mappedBy="service")
     */
    private $accommodations;


    public function __construct()
    {
        $this->serviceUser = new ArrayCollection();
        $this->supportGroup = new ArrayCollection();
        $this->serviceDevices = new ArrayCollection();
        $this->accommodations = new ArrayCollection();
    }

    public function __toString()
    {
        return strval($this->id);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * @return Collection|ServiceUser[]
     */
    public function getserviceUser(): Collection
    {
        return $this->serviceUser;
    }

    public function addServiceUser(ServiceUser $serviceUser): self
    {
        if (!$this->serviceUser->contains($serviceUser)) {
            $this->serviceUser[] = $serviceUser;
            $serviceUser->setService($this);
        }

        return $this;
    }

    public function removeServiceUser(ServiceUser $serviceUser): self
    {
        if ($this->serviceUser->contains($serviceUser)) {
            $this->serviceUser->removeElement($serviceUser);
            // set the owning side to null (unless already changed)
            if ($serviceUser->getService() === $this) {
                $serviceUser->setService(null);
            }
        }

        return $this;
    }

    public function getPole(): ?Pole
    {
        return $this->pole;
    }

    public function setPole(?Pole $pole): self
    {
        $this->pole = $pole;

        return $this;
    }

    /**
     * @return Collection|SupportGroup[]
     */
    public function getSupportGroup(): Collection
    {
        return $this->supportGroup;
    }

    public function addSupportGroup(SupportGroup $supportGroup): self
    {
        if (!$this->supportGroup->contains($supportGroup)) {
            $this->supportGroup[] = $supportGroup;
            $supportGroup->setService($this);
        }

        return $this;
    }

    public function removeSupportGroup(SupportGroup $supportGroup): self
    {
        if ($this->supportGroup->contains($supportGroup)) {
            $this->supportGroup->removeElement($supportGroup);
            // set the owning side to null (unless already changed)
            if ($supportGroup->getService() === $this) {
                $supportGroup->setService(null);
            }
        }

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
        return Phone::getPhoneFormat($this->phone);
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = Phone::formatPhone($phone);

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(?string $zipCode): self
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getChief(): ?string
    {
        return $this->chief;
    }

    public function setChief(?string $chief): self
    {
        $this->chief = $chief;

        return $this;
    }

    public function getSupportAccess(): ?int
    {
        return $this->supportAccess;
    }

    public function setSupportAccess(?int $supportAccess): self
    {
        $this->supportAccess = $supportAccess;

        return $this;
    }

    public function getSupportAccessList()
    {
        return self::SUPPORT_ACCESS[$this->supportAccess];
    }

    public function getAccommodation(): ?bool
    {
        return $this->accommodation;
    }

    public function setAccommodation(?bool $accommodation): self
    {
        $this->accommodation = $accommodation;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return Collection|ServiceDevice[]
     */
    public function getServiceDevices(): Collection
    {
        return $this->serviceDevices;
    }

    public function addServiceDevice(ServiceDevice $serviceDevice): self
    {
        if (!$this->serviceDevices->contains($serviceDevice)) {
            $this->serviceDevices[] = $serviceDevice;
            $serviceDevice->setService($this);
        }

        return $this;
    }

    public function removeServiceDevice(ServiceDevice $serviceDevice): self
    {
        if ($this->serviceDevices->contains($serviceDevice)) {
            $this->serviceDevices->removeElement($serviceDevice);
            // set the owning side to null (unless already changed)
            if ($serviceDevice->getService() === $this) {
                $serviceDevice->setService(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Accommodation[]
     */
    public function getAccommodations(): Collection
    {
        return $this->accommodations;
    }

    public function addAccommodation(Accommodation $accommodation): self
    {
        if (!$this->accommodations->contains($accommodation)) {
            $this->accommodations[] = $accommodation;
            $accommodation->setService($this);
        }

        return $this;
    }

    public function removeAccommodation(Accommodation $accommodation): self
    {
        if ($this->accommodations->contains($accommodation)) {
            $this->accommodations->removeElement($accommodation);
            // set the owning side to null (unless already changed)
            if ($accommodation->getService() === $this) {
                $accommodation->setService(null);
            }
        }

        return $this;
    }
}
