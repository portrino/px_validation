<?php

declare(strict_types=1);

namespace Portrino\PxValidation\Reflection;

use Portrino\PxValidation\Domain\Validator\TypoScriptValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Configuration\Exception\NoServerRequestGivenException;
use TYPO3\CMS\Extbase\Reflection\ClassSchema\Method;

/**
 * Transparently augments the validators of controller action arguments with a
 * TypoScriptValidator, based on the px_validation settings. This is the
 * agnostic counterpart to the TypoScriptValidationTrait: it works for ANY
 * controller (including third-party ones) because Extbase reads argument
 * validators from the ClassSchema in ActionController::initializeActionMethodValidators().
 *
 * Implementation notes:
 *  - It does NOT re-implement the core parsing (no full copy). It rides on the
 *    fully parsed core Method and only rebuilds it from its PUBLIC getters in
 *    order to append our validator definition. No reflection into private state.
 *  - The TypoScript settings are read lazily at getMethod() time, so the cached
 *    ClassSchema stays request-agnostic, and the actual rules are resolved live
 *    by the TypoScriptValidator during validation.
 *
 * @internal rides on the @internal ClassSchema API of Extbase.
 */
class ClassSchema extends \TYPO3\CMS\Extbase\Reflection\ClassSchema
{
    protected string $pxClassName;

    public function __construct(string $className)
    {
        parent::__construct($className);
        // the parent stores $className privately, keep an accessible copy
        $this->pxClassName = $className;
    }

    public function getMethod(string $methodName): Method
    {
        return $this->decorateMethod(parent::getMethod($methodName));
    }

    /**
     * @return array<string, Method>
     */
    public function getMethods(): array
    {
        $methods = [];
        foreach (parent::getMethods() as $methodName => $method) {
            $methods[$methodName] = $this->decorateMethod($method);
        }
        return $methods;
    }

    /**
     * Rebuilds the Method from its public API and appends the TypoScriptValidator
     * definition to every parameter that has px_validation configuration.
     */
    private function decorateMethod(Method $method): Method
    {
        $injectedValidators = $this->getInjectedValidators($method->getName());
        if ($injectedValidators === []) {
            return $method;
        }

        $params = [];
        foreach ($method->getParameters() as $parameter) {
            $parameterDefinition = [
                'type' => $parameter->getType(),
                'array' => $parameter->isArray(),
                'optional' => $parameter->isOptional(),
                'hasDefaultValue' => $parameter->hasDefaultValue(),
                'defaultValue' => $parameter->getDefaultValue(),
                'ignoreValidation' => $parameter->ignoreValidation(),
                'validators' => $parameter->getValidators(),
            ];

            if (isset($injectedValidators[$parameter->getName()])) {
                $parameterDefinition['validators'] = array_merge(
                    $parameterDefinition['validators'],
                    $injectedValidators[$parameter->getName()]
                );
            }

            $params[$parameter->getName()] = $parameterDefinition;
        }

        return new Method(
            $method->getName(),
            [
                'public' => $method->isPublic(),
                'protected' => $method->isProtected(),
                'private' => $method->isPrivate(),
                'params' => $params,
            ],
            $this->pxClassName
        );
    }

    /**
     * Returns, per argument name, the validator definitions to inject for the
     * given action method (one TypoScriptValidator per configured argument).
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function getInjectedValidators(string $methodName): array
    {
        $settings = $this->getSettings();
        $methodConfig = $settings[$this->pxClassName][$methodName] ?? null;
        if (!is_array($methodConfig)) {
            return [];
        }

        $injectedValidators = [];
        foreach ($methodConfig as $argumentName => $argumentConfig) {
            if (!is_array($argumentConfig)) {
                continue;
            }
            $injectedValidators[(string)$argumentName] = [
                [
                    // ActionController::initializeActionMethodValidators() reads
                    // 'className' + 'options' from each validator definition.
                    'name' => TypoScriptValidator::class,
                    'className' => TypoScriptValidator::class,
                    'options' => [
                        'className' => $this->pxClassName,
                        'methodName' => $methodName,
                        'argumentName' => (string)$argumentName,
                    ],
                ],
            ];
        }
        return $injectedValidators;
    }

    /**
     * @return array<string, mixed>
     */
    private function getSettings(): array
    {
        try {
            $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
            return $configurationManager->getConfiguration(
                ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                'PxValidation'
            ) ?? [];
        } catch (NoServerRequestGivenException) {
            // e.g. CLI without a request: no TypoScript driven validation
            return [];
        }
    }
}
