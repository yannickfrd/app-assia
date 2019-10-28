<?php

namespace App\Entity;

use App\Entity\Pole;
use App\Utils\Phone;
use App\Entity\Department;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

class UserSearch
{
    /**
     * @var string|null
     */
    private $firstname;

    /**
     * @var string|null
     */
    private $lastname;

    private $username;

    /**
     * @var int|null
     */
    private $roleUser;

    /**
     * @var string|null
     * @Assert\Regex(pattern="^0[1-9]([-._/ ]?[0-9]{2}){4}$^", match=true, message="Le numéro de téléphone est incorrect.")
     */
    private $phone;

    /**
     * @var string|null
     * @Assert\Email(message="Email invalide.")
     */
    private $email;

    /**
     * @var ArrayCollection
     */
    private $department;

    /**
     * @var int|null
     */
    private $pole;

    public function __construct()
    {
        $this->department = new ArrayCollection();
    }

    /**
     * @return string|null
     */
    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getRoleUser(): ?int
    {
        return $this->roleUser;
    }

    public function setRoleUser(?int $roleUser): self
    {
        $this->roleUser = $roleUser;

        return $this;
    }

    public function getRoleUserType()
    {
        return self::GENDER[$this->roleUser];
    }


    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {

        $this->phone = Phone::formatPhone($phone);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDepartment(): ?ArrayCollection
    {
        return $this->department;
    }

    public function setDepartment(?ArrayCollection $department): self
    {
        $this->department = $department;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getPole(): ?Pole
    {
        return $this->pole;
    }

    public function setPole(?Pole $pole): self
    {
        $this->pole = $pole;

        return $this;
    }
}
