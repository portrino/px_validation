<?php

declare(strict_types=1);

namespace Portrino\PxValidation\Domain\Validator;

/**
 * Validator that resolves its rule set from the px_validation TypoScript
 * settings, addressed by controller class, action method, and argument name.
 *
 * Note: "overwriteDefaultValidation" is no longer an option of this validator.
 * Whether the type-based base validator conjunction is kept or replaced is now
 * decided when the validator is attached to the controller argument
 * (see Portrino\PxValidation\Mvc\Controller\TypoScriptValidationTrait).
 */
class TypoScriptValidator extends AbstractValidator
{
    /**
     * @var array
     */
    protected $supportedOptions = [
        'className' => ['', 'Name of the controller class which should be validated', 'string'],
        'methodName' => ['', 'Name of the action method which should be validated', 'string'],
        'argumentName' => ['', 'Name of the argument which should be validated', 'string'],
    ];

    /**
     * @return array<string, mixed>
     */
    protected function getValidationFields(): array
    {
        $className = (string)$this->options['className'];
        $methodName = (string)$this->options['methodName'];
        $argumentName = (string)$this->options['argumentName'];

        return $this->settings[$className][$methodName][$argumentName] ?? [];
    }
}
