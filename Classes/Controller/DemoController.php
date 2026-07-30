<?php

namespace Portrino\PxValidation\Controller;

use Portrino\PxValidation\Domain\Model\Address;
use Portrino\PxValidation\Domain\Repository\AddressRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

class DemoController extends ActionController
{
    public function __construct(
        protected readonly AddressRepository $addressRepository,
        protected readonly PersistenceManager $persistenceManager
    ) {}

    public function newAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    public function createAction(Address $newAddress): ResponseInterface
    {
        $address = $this->addressRepository->findOneByEmailIgnoreHidden($newAddress->getEmail()) ?? $newAddress;
        if ($address->getUid() === null) {
            $address->setHidden(false);
            $this->addressRepository->add($address);

            $this->addFlashMessage(
                $this->getTranslation(
                    'flash.ok.created',
                    'Address record created',
                )
            );
        } else {
            $address->setHidden(false);
            $address->setName($newAddress->getName());
            $this->addressRepository->update($address);

            $this->addFlashMessage(
                $this->getTranslation(
                    'flash.info.updated',
                    'Address record updated',
                ),
                '',
                ContextualFeedbackSeverity::INFO
            );
        }
        $this->persistenceManager->persistAll();

        return $this->redirect('finish');
    }

    /**
     * This action only exists, so that the newAction can be rendered cached
     */
    public function finishAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * Deactivate error messages in flash messages
     *
     * @return bool
     */
    protected function getErrorFlashMessage(): bool
    {
        return false;
    }

    protected function getTranslation(string $key, string $default = ''): string
    {
        return LocalizationUtility::translate($key, 'PxValdidation') ?? $default;
    }
}
