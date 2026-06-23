<?php

return [

    \FriendsOfTYPO3\TtAddress\Domain\Model\Address::class => [
        'subclasses' => [
            '\Portrino\PxValidation\Domain\Model\Address' => \Portrino\PxValidation\Domain\Model\Address::class,
        ],
    ],
    \Portrino\PxValidation\Domain\Model\Address::class => [
        'tableName' => 'tt_address',
    ],

];
