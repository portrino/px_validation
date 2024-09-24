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
use Portrino\PxValidation\Validation\ValidatorResolver;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\Validate;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Validation\Exception\NoSuchValidatorException;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ObjectValidatorInterface;

/**
 * Class AbstractValidator
 */
abstract class AbstractValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{
    /**
     * Contains the settings of the current extension
     *
     * @var array<string, mixed>
     */
    protected array $settings = [];

    /**
     * Contains the settings of the current extension
     *
     * @var array<string, mixed>
     */
    protected array $validationFields = [];

    /**
     * @var ConfigurationManagerInterface
     */
    protected ConfigurationManagerInterface $configurationManager;

    /**
     * @var ValidatorResolver
     */
    protected $validatorResolver;

    public function __construct()
    {
        $this->configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $this->settings = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'PxValidation'
        );
        $this->validatorResolver = GeneralUtility::makeInstance(ValidatorResolver::class);
    }

    /**
     * @param mixed $value
     * @throws Exception
     */
    protected function isValid(mixed $value): void
    {
        $this->validationFields = $this->getValidationFields();
        $object = $value;

        $objectValidators = new ObjectStorage();
        /** @var GenericObjectValidator $objectValidator */
        $objectValidator = GeneralUtility::makeInstance(GenericObjectValidator::class);
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
                            'Invalid typoscript validation rule in ' . $object . '::' . $validationRule . ': Could not resolve class name for  validator "' . $validateAnnotation->validator . '".',
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
                    $typoScriptChildValidator = GeneralUtility::makeInstance(TypoScriptChildValidator::class);

                    $typoScriptChildValidator->setValidationFields($validationRules);
                    $typoScriptChildValidator->setChildPropertyName($validationField);
                    $callable = [$object, 'get' . $validationField];
                    if (is_callable($callable)) {
                        $child = call_user_func_array($callable, []);
                        $typoScriptChildValidator->setChildObject($child);
                        $objectValidators->attach($typoScriptChildValidator);
                    }
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
                                'Invalid typoscript validation rule in ' . $object . '::' . $validationField . ': Could not resolve class name for  validator "' . $validateAnnotation->validator . '".',
                                1241098027
                            );
                        }
                        $objectValidator->addPropertyValidator($validationField, $newValidator);
                    }
                }
            }
        }
        unset($objectValidator);

        /** @var ObjectValidatorInterface $objectValidator */
        foreach ($objectValidators as $objectValidator) {
            if ($objectValidator instanceof TypoScriptChildValidator) {
                $typoScriptChildValidator = $objectValidator;
                $result = $typoScriptChildValidator->validate($typoScriptChildValidator->getChildObject());
                $this->result->forProperty($typoScriptChildValidator->getChildPropertyName())->merge($result);
            } else {
                $this->result->merge($objectValidator->validate($object));
            }
        }
    }

    /**
     * returns the array of validation fields from typoScript
     *
     * @return array<string, mixed>
     */
    abstract protected function getValidationFields(): array;
}
