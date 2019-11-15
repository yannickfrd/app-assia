<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class UserResetPass
{

    /**
     * @Assert\NotNull(message="Le login ne doit pas être vide.")
     */
    private $username;

    /**
     * @Assert\NotNull(message="L'adresse email ne doit pas être vide.")
     * @Assert\Email(message="L'adresse email n'est pas valide.")
     */
    private $email;

    /**
     * @Assert\Length(min=6, minMessage="Le mot de passe est trop court (6 caractères minimum).")
     */
    private $password;

    /**
     * @Assert\EqualTo(propertyPath="password", message="Mot de passe différent de la confirmation.")
     */
    private $confirmPassword;

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    public function setConfirmPassword(string $confirmPassword): self
    {
        $this->confirmPassword = $confirmPassword;

        return $this;
    }
}
