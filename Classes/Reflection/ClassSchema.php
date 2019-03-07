<?php

namespace Portrino\PxValidation\Reflection;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * A class schema
 * @internal only to be used within Extbase, not part of TYPO3 Core API.
 */
class ClassSchema extends \TYPO3\CMS\Extbase\Reflection\ClassSchema
{
    /**
     * @var array
     */
    protected $methods;

    public function __construct(string $className)
    {
        parent::__construct($className);
        $this->methods = parent::getMethods();
    }

    public function addValidator($methodName, $argumentName, $validatorClass, $validatorOptions)
    {
        $this->methods[$methodName]['params'][$argumentName]['validators'][] = [
            'name' => $validatorClass,
            'options' => $validatorOptions,
            'className' => $validatorClass,
        ];
    }

    /**
     * @param string $name
     * @return array
     */
    public function getMethod(string $name): array
    {
        return $this->methods[$name] ?? [];
    }

    /**
     * @return array
     */
    public function getMethods(): array
    {
        return $this->methods;
    }
}
