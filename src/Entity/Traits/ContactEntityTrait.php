<?php

namespace App\Entity\Traits;

use App\Service\Phone;
use Symfony\Component\Validator\Constraints as Assert;

trait ContactEntityTrait
{
    /**
     * @ORM\Column(name="email", type="string", length=100, nullable=true)
     * @Assert\Regex(pattern="^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$^", match=true, message="L'adresse email n'est pas valide !")
     */
    private $email;
    // * @Assert\Email(message="L'adresse email n'est pas valide.")

    /**
     * @ORM\Column(name="phone1", type="string", length=20, nullable=true)
     * @Assert\Regex(pattern="^0[1-9]([-._/ ]?[0-9]{2}){4}$^", match=true, message="Le numéro de téléphone est incorrect.")
     */
    private $phone1;

    /**
     * @ORM\Column(name="phone2", type="string", length=20, nullable=true)
     * @Assert\Regex(pattern="^0[1-9]([-._/ ]?[0-9]{2}){4}$^", match=true, message="Le numéro de téléphone est incorrect.")
     */
    private $phone2;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone1(): ?string
    {
        return $this->phone1;
        // return Phone::getPhoneFormat($this->phone1);
    }

    public function setPhone1(?string $phone1): self
    {
        $this->phone1 = Phone::formatPhone($phone1);

        return $this;
    }

    public function getPhone2(): ?string
    {
        return $this->phone2;
        // return Phone::getPhoneFormat($this->phone2);
    }

    public function setPhone2(?string $phone2): self
    {
        $this->phone2 = Phone::formatPhone($phone2);

        return $this;
    }
}
