<?php

namespace App\Model;

use Doctrine\ORM\Mapping\Column;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;


#[Entity, Table(name: 'payments')]
class Payment
{
    #[Id, Column(type: 'integer'), GeneratedValue(strategy: 'AUTO')]
    private $id;

    #[Column(name: 'sum', type: 'float', nullable: false)]
    private float $sum;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSum(): ?string
    {
        return $this->sum;
    }

    public function setSum(string $sum): self
    {
        $this->sum = $sum;

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
            'sum' => $this->sum,
        ];
    }

}

