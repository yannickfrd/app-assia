<?php

namespace App\Form\Model;

use Symfony\Component\Validator\Constraints as Assert;

class UserChangeInfo
{
    /**
     * @Assert\NotNull(message="L'adresse email ne doit pas être vide.")
     * @Assert\Email(message="L'adresse email n'est pas valide.")
     */
    private $email;


    /**
     * @Assert\Length(min = 10,max = 15)
     */
    private $phone;

    /**
     * @Assert\Length(min = 10,max = 15)
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


    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhone2(): ?string
    {
        return $this->phone2;
    }

    public function setPhone2(?string $phone2): self
    {
        $this->phone2 = $phone2;

        return $this;
    }
}
