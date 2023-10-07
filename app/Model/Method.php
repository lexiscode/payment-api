<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Column;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;


#[Entity, Table(name: 'methods')]
class Method
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[Column(type: 'string', unique: true, nullable: false, length: 255)]
    private $name;

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


    /**
     * Doctrine entities are often designed to be serialized in a specific way, and by default, 
     * some properties may not be accessible for serialization.
     * 
     * Therefore, this method returns an array representation of the entity's data.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }

}

