<?php

namespace App\Form\Model\SiSiao;

class SiSiaoLogin
{
    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    public function __construct()
    {
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

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
}
