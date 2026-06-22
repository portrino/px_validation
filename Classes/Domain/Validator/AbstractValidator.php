<?php

declare(strict_types=1);

namespace Portrino\PxValidation\Domain\Validator;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Validation\Exception\NoSuchValidatorException;
use TYPO3\CMS\Extbase\Validation\Validator\GenericObjectValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ObjectValidatorInterface;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\CMS\Extbase\Validation\ValidatorResolver;

/**
 * Base for the TypoScript driven validators.
 *
 * Since TYPO3 v14 Extbase no longer ships the doctrine/annotations based
 * DocParser, and \TYPO3\CMS\Extbase\Validation\ValidatorResolver no longer
 * exposes parseValidatorAnnotation(). The validation rules are therefore read
 * from a plain, structured TypoScript notation instead of an annotation string:
 *
 *     <ruleIndex> {
 *         validator = <ValidatorName|FQCN>
 *         options {
 *             <optionKey> = <optionValue>
 *         }
 *     }
 */
abstract class AbstractValidator extends \TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator
{
    /**
     * @var array<string, mixed>
     */
    protected array $settings = [];

    /**
     * @var array<string, mixed>
     */
    protected array $validationFields = [];

    protected ?ValidatorResolver $validatorResolver = null;

    public function __construct()
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $this->settings = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'PxValidation'
        );
        // v14: the core resolver still offers createValidator(); parseValidatorAnnotation() is gone.
        $this->validatorResolver = GeneralUtility::makeInstance(ValidatorResolver::class);
    }

    /**
     * Builds the validator chain from the structured TypoScript configuration
     * and merges the results into $this->result.
     *
     * @throws \Exception
     */
    protected function isValid(mixed $value): void
    {
        $this->validationFields = $this->getValidationFields();

        $objectValidators = new ObjectStorage();

        /** @var GenericObjectValidator $objectValidator */
        $objectValidator = GeneralUtility::makeInstance(GenericObjectValidator::class);
        $objectValidator->setRequest($this->getRequest());
        $objectValidators->attach($objectValidator);

        // object level validators (validate the whole object)
        foreach ($this->getRules($this->validationFields['objectValidators'] ?? null) as $rule) {
            $objectValidators->attach($this->createValidatorFromRule($rule, (string)$value));
        }

        // property level validators
        if (is_array($this->validationFields['propertyValidators'] ?? null)) {
            foreach ($this->validationFields['propertyValidators'] as $propertyName => $rules) {
                if (!is_array($rules)) {
                    continue;
                }

                // nested child object -> delegate to a TypoScriptChildValidator
                if (array_key_exists('propertyValidators', $rules)) {
                    $objectValidators->attach($this->buildChildValidator($value, (string)$propertyName, $rules));
                    continue;
                }

                if (!property_exists($value, (string)$propertyName)) {
                    throw new \Exception(
                        'The property "' . $propertyName . '" does not exist for class: "' . get_class($value) . '"',
                        1738336088
                    );
                }

                foreach ($this->getRules($rules) as $rule) {
                    $objectValidator->addPropertyValidator(
                        (string)$propertyName,
                        $this->createValidatorFromRule($rule, (string)$value)
                    );
                }
            }
        }
        unset($objectValidator);

        /** @var ObjectValidatorInterface $validator */
        foreach ($objectValidators as $validator) {
            if ($validator instanceof TypoScriptChildValidator) {
                $childResult = $validator->validate($validator->getChildObject());
                $this->result->forProperty($validator->getChildPropertyName())->merge($childResult);
            } else {
                $this->result->merge($validator->validate($value));
            }
        }
    }

    /**
     * Returns the array of validation fields for the current context.
     *
     * @return array<string, mixed>
     */
    abstract protected function getValidationFields(): array;

    /**
     * Normalizes a (possibly null) rule container into a list of rule arrays.
     *
     * @param mixed $rules
     * @return array<int, array<string, mixed>>
     */
    private function getRules(mixed $rules): array
    {
        if (!is_array($rules)) {
            return [];
        }
        // only keep numerically indexed rule definitions
        return array_values(array_filter(
            $rules,
            static fn(mixed $rule, mixed $key): bool => is_numeric($key) && is_array($rule),
            ARRAY_FILTER_USE_BOTH
        ));
    }

    /**
     * @param array<string, mixed> $rule
     */
    private function createValidatorFromRule(array $rule, string $context): ValidatorInterface
    {
        $validatorName = (string)($rule['validator'] ?? '');
        $options = is_array($rule['options'] ?? null) ? $rule['options'] : [];

        $validator = $this->validatorResolver->createValidator(
            $validatorName,
            $this->normalizeOptions($options),
            $this->getRequest()
        );

        if ($validator === null) {
            throw new NoSuchValidatorException(
                'Invalid TypoScript validation rule in ' . $context . ': could not resolve validator "'
                . $validatorName . '".',
                1241098027
            );
        }

        return $validator;
    }

    /**
     * @param array<string, mixed> $rules
     */
    private function buildChildValidator(mixed $value, string $propertyName, array $rules): TypoScriptChildValidator
    {
        /** @var TypoScriptChildValidator $childValidator */
        $childValidator = GeneralUtility::makeInstance(TypoScriptChildValidator::class);
        $childValidator->setRequest($this->getRequest());
        $childValidator->setValidationFields($rules);
        $childValidator->setChildPropertyName($propertyName);

        $getter = [$value, 'get' . ucfirst($propertyName)];
        if (!is_callable($getter)) {
            throw new \Exception(
                'Getter for property "' . $propertyName . '" not callable in class: "' . get_class($value) . '"',
                1738340860
            );
        }
        $childValidator->setChildObject($getter());

        return $childValidator;
    }

    /**
     * TypoScript delivers every scalar as a string. We only coerce the literal
     * strings "true"/"false" to booleans (the most common foot-gun, e.g. the
     * BooleanValidator "is" option). Everything else is passed through; core
     * validators tolerate numeric strings. Project validators that require
     * strictly typed options should cast them themselves.
     *
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function normalizeOptions(array $options): array
    {
        foreach ($options as $key => $optionValue) {
            if (is_array($optionValue)) {
                $options[$key] = $this->normalizeOptions($optionValue);
            } elseif (is_string($optionValue) && in_array(strtolower($optionValue), ['true', 'false'], true)) {
                $options[$key] = strtolower($optionValue) === 'true';
            }
        }
        return $options;
    }
}
