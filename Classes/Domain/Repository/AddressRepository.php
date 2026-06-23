<?php

namespace Portrino\PxValidation\Domain\Repository;

use FriendsOfTYPO3\TtAddress\Domain\Repository\AddressRepository as TtAddressRepository;
use Portrino\PxValidation\Domain\Model\Address;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

class AddressRepository extends TtAddressRepository
{
    /**
     * @param string $email
     * @return QueryResultInterface<mixed, Address>
     */
    public function findByEmailIgnoreHidden(string $email): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setIgnoreEnableFields(true);
        return $query->matching(
            $query->equals('email', $email)
        )->execute();
    }

    public function findOneByEmailIgnoreHidden(string $email): ?Address
    {
        /* @var Address|null $address */
        $address = $this->findByEmailIgnoreHidden($email)->getFirst();
        return $address;
    }
}
