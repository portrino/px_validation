<?php

namespace Portrino\PxValidation\Validation;

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

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use Portrino\PxValidation\Domain\Validator\TypoScriptValidator;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Class ValidatorResolver
 *
 * @package Portrino\PxValidation\Validation
 */
class ValidatorResolver extends \TYPO3\CMS\Extbase\Validation\ValidatorResolver
{

    /**
     * Contains the settings of the current extension
     *
     * @var array
     */
    protected $settings;

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @param ConfigurationManagerInterface $configurationManager
     * @return void
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager)
    {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'PxValidation'
        );
    }

    /**
     * @param string $validateValue
     * @param array $validatorOptions
     * @return array
     */
    public function parseValidatorAnnotation($validateValue, $validatorOptions = [])
    {
        $context = '';
        if (array_key_exists('className', $validatorOptions) && array_key_exists('argumentName', $validatorOptions)) {
            $context = 'property ' . $validatorOptions['className'] . '::$' . $validatorOptions['argumentName'];
        }
        $parser = new DocParser();
        return $parser->parse($validateValue, $context);
    }

    /**
     * We need to override this method to get the validate annotations also from TypoScript Configuration
     *
     * @param string $className
     * @param string $methodName
     *
     * @return array
     */
    public function getMethodValidateAnnotations($className, $methodName)
    {
        var_dump('getMethodValidateAnnotations');exit;
        $validateAnnotations = parent::getMethodValidateAnnotations($className, $methodName);
        $methodParameters = $this->reflectionService->getMethodParameters($className, $methodName);
        foreach ($methodParameters as $argumentName => $methodParameterValue) {
            if (isset($this->settings[$className][$methodName][$argumentName])) {
                if (isset($this->settings[$className][$methodName][$argumentName]['overwriteDefaultValidation'])) {
                    $overwriteDefaultValidation = (Boolean)$this->settings[$className][$methodName][$argumentName]['overwriteDefaultValidation'];
                } else {
                    $overwriteDefaultValidation = false;
                }

                array_push($validateAnnotations, [
                    'argumentName' => $argumentName,
                    'validatorName' => TypoScriptValidator::class,
                    'validatorOptions' => [
                        'className' => $className,
                        'methodName' => $methodName,
                        'argumentName' => $argumentName,
                        'overwriteDefaultValidation' => $overwriteDefaultValidation
                    ]
                ]);
            }
        }

        return $validateAnnotations;
    }
}
