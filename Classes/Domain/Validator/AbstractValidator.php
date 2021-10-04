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

use Exception;
use TYPO3\CMS\Extbase\Annotation\Inject;
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Validation\Exception\NoSuchValidatorException;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;

/**
 * Class AbstractValidator
 *
 * @package Portrino\PxValidation\Domain\Validator
 */
abstract class AbstractValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{

    /**
     * @var \TYPO3\CMS\Extbase\Reflection\ReflectionService
     * @Inject
     */
    protected $reflectionService = null;

    /**
     *
     * @var \TYPO3\CMS\Extbase\Object\ObjectManagerInterface
     * @Inject
     */
    protected $objectManager = null;

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
     * @var ConfigurationManager
     */
    protected $configurationManager;
    /**
     * @var \Portrino\PxValidation\Validation\ValidatorResolver
     * @Inject
     */
    protected $validatorResolver;

    /**
     * @param ConfigurationManager $configurationManager
     * @return void
     */
    public function injectConfigurationManager(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(
            ConfigurationManager::CONFIGURATION_TYPE_SETTINGS
        );
    }

    /**
     * generic isValid method
     *
     * @param mixed $object
     * @throws Exception
     *
     * @return bool
     */
    public function isValid($object)
    {
        $result = true;
        $this->validationFields = $this->getValidationFields();

        $objectValidators = new ObjectStorage();
        /** @var GenericObjectValidator $objectValidator */
        $objectValidator = $this->objectManager->get(GenericObjectValidator::class);
        $objectValidators->attach($objectValidator);

        // add the configured object validators
        if (array_key_exists('objectValidators', $this->validationFields) &&
            is_array($this->validationFields['objectValidators'])) {
            foreach ($this->validationFields['objectValidators'] as $validationRule) {
                $parsedAnnotation = $this->validatorResolver->parseValidatorAnnotation($validationRule, $this->options);
                /** @var Validate $validateAnnotation */
                foreach ($parsedAnnotation as $validateAnnotation) {
                    $newValidator = $this->validatorResolver->createValidator(
                        $validateAnnotation->validator,
                        $validateAnnotation->options
                    );
                    if ($newValidator === null) {
                        throw new NoSuchValidatorException(
                            'Invalid typoscript validation rule in ' . $object . '::' . $validationRule . ': Could not resolve class name for  validator "' . $validatorConfiguration['validatorName'] . '".',
                            1241098027
                        );
                    }
                    $objectValidators->attach($newValidator);
                }
            }
        }

        // add the configured property validators
        if (array_key_exists('propertyValidators', $this->validationFields) &&
            is_array($this->validationFields['propertyValidators'])) {
            foreach ($this->validationFields['propertyValidators'] as $validationField => $validationRules) {

                /**
                 *  if the property to validate is a child property then create a new $typoScriptChildValidator
                 *  with the validation config of the child
                 */
                if (array_key_exists('propertyValidators', $validationRules)) {
                    /** @var TypoScriptChildValidator $typoScriptChildValidator */
                    $typoScriptChildValidator = $this->objectManager->get(TypoScriptChildValidator::class);


                    $typoScriptChildValidator->setValidationFields($validationRules);
                    $typoScriptChildValidator->setChildPropertyName($validationField);
                    $child = call_user_func_array([$object, 'get' . $validationField], []);
                    $typoScriptChildValidator->setChildObject($child);
                    $objectValidators->attach($typoScriptChildValidator);
                    continue;
                }

                // only check if it is not a objectValidator (just check propertyValidators)
                if (!property_exists($object, $validationField) && ($validationField !== 'objectValidators')) {
                    throw new Exception(
                        'The property: "' . $validationField . '" does not exist for class: "' . get_class($object) . '"'
                    );
                }
                foreach ($validationRules as $validationRule) {
                    $parsedAnnotation = $this->validatorResolver->parseValidatorAnnotation(
                        $validationRule,
                        $this->options
                    );
                    /** @var Validate $validateAnnotation */
                    foreach ($parsedAnnotation as $validateAnnotation) {
                        $newValidator = $this->validatorResolver->createValidator(
                            $validateAnnotation->validator,
                            $validateAnnotation->options
                        );
                        if ($newValidator === null) {
                            throw new NoSuchValidatorException(
                                'Invalid typoscript validation rule in ' . $object . '::' . $validationField . ': Could not resolve class name for  validator "' . $validateAnnotation['validatorName'] . '".',
                                1241098027
                            );
                        }
                        $objectValidator->addPropertyValidator($validationField, $newValidator);
                    }
                }
            }
        }

        foreach ($objectValidators as $objectValidator) {
            if ($objectValidator instanceof TypoScriptChildValidator) {
                $typoScriptChildValidator = $objectValidator;
                $result = $typoScriptChildValidator->validate($typoScriptChildValidator->getChildObject());
                $this->result->forProperty($typoScriptChildValidator->getChildPropertyName())->merge($result);
            } else {
                $this->result->merge($objectValidator->validate($object));
            }

            if ($this->result->hasErrors()) {
                $result = false;
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
