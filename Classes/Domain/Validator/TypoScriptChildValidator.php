<?php

declare(strict_types=1);

namespace Portrino\PxValidation\Domain\Validator;

/**
 * Validates a nested child object with an explicitly injected rule set.
 */
class TypoScriptChildValidator extends TypoScriptValidator
{
    protected mixed $childPropertyName = null;

    protected mixed $childObject = null;

    /**
     * @param array<string, mixed> $validationFields
     */
    public function setValidationFields(array $validationFields): void
    {
        $this->validationFields = $validationFields;
    }

    /**
     * @return array<string, mixed>
     */
    protected function getValidationFields(): array
    {
        return $this->validationFields;
    }

    public function getChildPropertyName(): mixed
    {
        return $this->childPropertyName;
    }

    public function setChildPropertyName(mixed $childPropertyName): void
    {
        $this->childPropertyName = $childPropertyName;
    }

    public function getChildObject(): mixed
    {
        return $this->childObject;
    }

    public function setChildObject(mixed $childObject): void
    {
        $this->childObject = $childObject;
    }
}
