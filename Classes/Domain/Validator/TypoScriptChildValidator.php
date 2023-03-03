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
 * Class TypoScriptChildValidator
 * @package Portrino\PxValidation\Domain\Validator
 */
class TypoScriptChildValidator extends \Portrino\PxValidation\Domain\Validator\TypoScriptValidator
{

    /**
     * the child property name which will be validated
     *
     * @var mixed
     */
    protected $childPropertyName;


    /**
     * the child object to validate
     *
     * @var mixed
     */
    protected $childObject;

    /**
     * @param array $validationFields
     */
    public function setValidationFields(array $validationFields): void
    {
        $this->validationFields = $validationFields;
    }

    /**
     * returns the array of validation fields from typoScript
     *
     * @return array
     */
    protected function getValidationFields(): array
    {
        return $this->validationFields;
    }

    /**
     * @return mixed
     */
    public function getChildPropertyName(): mixed
    {
        return $this->childPropertyName;
    }

    /**
     * @param mixed $childPropertyName
     */
    public function setChildPropertyName(mixed $childPropertyName): void
    {
        $this->childPropertyName = $childPropertyName;
    }

    /**
     * @return mixed
     */
    public function getChildObject(): mixed
    {
        return $this->childObject;
    }

    /**
     * @param mixed $childObject
     */
    public function setChildObject(mixed $childObject): void
    {
        $this->childObject = $childObject;
    }
}
