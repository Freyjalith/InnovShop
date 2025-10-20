<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Token;

#[ORM\Entity]
#[ORM\Table(name: 'payum_token')]
class PaymentToken extends Token
{
}