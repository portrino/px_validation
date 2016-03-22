<?php
namespace Portrino\PxValidation\Domain\Validator;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Andre Wuttig <wuttig@portrino.de>, portrino GmbH
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Class AbstractValidator
 *
 * @package Portrino\PxValidation\Domain\Validator
 */
abstract class AbstractValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator {

    /**
	 * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService
     * @inject
	 */
	protected $reflectionService = NULL;

    /**
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @inject
     */
    protected $objectManager = NULL;

    /**
     * Contains the settings of the current extension
     *
     * @var array
     */
    protected $settings;

    /**
     * Contains the settings of the current extension
     *
     * @var array
     */
    protected $validationFields;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManager
     * @inject
     */
    protected $configurationManager = NULL;
    /**
     * @var \Portrino\PxValidation\Validation\ValidatorResolver
     * @inject
     */
    protected $validatorResolver = NULL;

    /**
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager
     * @return void
     */
    public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager $configurationManager) {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManager::CONFIGURATION_TYPE_SETTINGS);
    }

    /**
     * generic isValid method
     *
     * @param mixed $object
     * @throws \Exception
     *
     * @return bool
     */
    public function isValid($object) {

        $result = TRUE;
        $this->validationFields = $this->getValidationFields();
        $objectValidators = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
        /** @var \TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator $objectValidator */
        $objectValidator = $this->objectManager->get(\TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator::class, array());
        $objectValidators->attach($objectValidator);

            // add the configured object validators
        if (array_key_exists('objectValidators', $this->validationFields) && is_array($this->validationFields['objectValidators'])) {
            foreach ($this->validationFields['objectValidators'] as $validationRule) {
                $parsedAnnotation = $this->validatorResolver->parseValidatorAnnotation($validationRule);
                foreach ($parsedAnnotation['validators'] as $validatorConfiguration) {
                    $newValidator = $this->validatorResolver->createValidator($validatorConfiguration['validatorName'], $validatorConfiguration['validatorOptions']);
                    if ($newValidator === NULL) {
                        throw new \TYPO3\CMS\Extbase\Validation\Exception\NoSuchValidatorException('Invalid typoscript validation rule in ' . $object . '::' . $validationRule . ': Could not resolve class name for  validator "' . $validatorConfiguration['validatorName'] . '".', 1241098027);
                    }
                    $objectValidators->attach($newValidator);
                }
            }
        }
            // add the configured property validators
        if (array_key_exists('propertyValidators', $this->validationFields) && is_array($this->validationFields['propertyValidators'])) {
            foreach ($this->validationFields['propertyValidators'] as $validationField => $validationRules) {
                    // only check if it is not a objectValidator (just check propertyValidators)
                if (!property_exists($object, $validationField) && ($validationField != 'objectValidators')) {
                    throw new \Exception('The property: "' . $validationField . '" does not exist for class: "' . get_class($object) . '"');
                }
                foreach ($validationRules as $validationRule) {
                    $parsedAnnotation = $this->validatorResolver->parseValidatorAnnotation($validationRule);
                    foreach ($parsedAnnotation['validators'] as $validatorConfiguration) {
                        $newValidator = $this->validatorResolver->createValidator($validatorConfiguration['validatorName'], $validatorConfiguration['validatorOptions']);
                        if ($newValidator === NULL) {
                            throw new \TYPO3\CMS\Extbase\Validation\Exception\NoSuchValidatorException('Invalid typoscript validation rule in ' . $object . '::' . $validationField . ': Could not resolve class name for  validator "' . $validatorConfiguration['validatorName'] . '".', 1241098027);
                        }
                        $objectValidator->addPropertyValidator($validationField, $newValidator);
                    }
                }
            }
        }

        foreach ($objectValidators as $objectValidator) {
            $this->result->merge($objectValidator->validate($object));
            if($this->result->hasErrors()) {
                $result = FALSE;
            }
        }
        return $result;
    }

    /**
     * returns the array of validation fields from typoScript
     *
     * @return array
     */
    abstract protected function getValidationFields();
}
