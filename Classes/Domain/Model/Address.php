<?php

namespace Portrino\PxValidation\Domain\Model;

use FriendsOfTYPO3\TtAddress\Domain\Model\Address as TtAddress;
use TYPO3\CMS\Extbase\Attribute\Validate;

class Address extends TtAddress
{
    protected bool $hidden = true;

    #[Validate(validator: 'NotEmpty')]
    #[Validate(validator: 'EmailAddress')]
    protected string $email = '';

    public function setEmail(string $email): void
    {
        $this->email = strtolower(trim($email));
    }
}
