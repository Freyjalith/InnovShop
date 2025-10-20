<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Payment as BasePayment;

#[ORM\Entity]
#[ORM\Table(name: 'payum_payment')]
class Payment extends BasePayment
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    protected $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}