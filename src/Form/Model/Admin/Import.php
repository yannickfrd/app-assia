<?php

namespace App\Form\Model\Admin;

use Doctrine\Common\Collections\ArrayCollection;

    class Import
    {
        /**
         * @var ArrayCollection
         */
        private $services;

        public function getServices(): ?ArrayCollection
        {
            return $this->services;
        }

        public function getServicesToString(): array
        {
            $services = [];

            foreach ($this->services as $service) {
                $services[] = $service->getName();
            }

            return $services;
        }

        public function setServices(?ArrayCollection $services): self
        {
            $this->services = $services;

            return $this;
        }
    }
