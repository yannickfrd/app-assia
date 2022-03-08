<?php

namespace App\Form\Model\Security;

use Symfony\Component\Validator\Constraints as Assert;

class UserInitPassword
{
    private $password;

    /**
     * @Assert\EqualTo(propertyPath="password", message="Mot de passe différent de la confirmation.")
     */
    private $confirmPassword;

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
