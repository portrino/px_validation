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
 * Class TypoScriptValidator
 *
 * @package Portrino\PxValidation\Domain\Validator
 */
class TypoScriptValidator extends \Portrino\PxValidation\Domain\Validator\AbstractValidator
{

    /**
     * @var array
     */
    protected $supportedOptions = [
        'className' => ['', 'Name of the controller class which should be validate', 'string'],
        'methodName' => ['', 'Name of the action method which should be validate', 'string'],
        'argumentName' => ['', 'Name of the argument which should be validate', 'string'],
        'overwriteDefaultValidation' => [
            '',
            'If TRUE the validation rules defined in the property, model or controller are overwritten (will not be executed).',
            'boolean'
        ]
    ];

    /**
     * returns the array of validation fields from typoscript
     *
     * @return array
     */
    protected function getValidationFields(): array
    {
        $result = [];
        $className = $this->options['className'];
        $methodName = $this->options['methodName'];
        $argumentName = $this->options['argumentName'];

        if (isset($this->settings[$className][$methodName][$argumentName])) {
            $result = $this->settings[$className][$methodName][$argumentName];
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function overwriteDefaultValidation(): bool
    {
        return (bool)$this->options['overwriteDefaultValidation'];
    }
}
