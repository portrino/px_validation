<?php
namespace Portrino\PxValidation\Validation;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Andre Wuttig <wuttig@portrino.de>, portrino GmbH
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
 * Class ValidatorResolver
 *
 * @package Portrino\PxValidation\Validation
 */
class ValidatorResolver extends \TYPO3\CMS\Extbase\Validation\ValidatorResolver {

    /**
     * Contains the settings of the current extension
     *
     * @var array
     */
    protected $settings;

    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @param \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager
     * @return void
     */
    public function injectConfigurationManager(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface $configurationManager) {
        $this->configurationManager = $configurationManager;
        $this->settings = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,'PxValidation');
    }

    /**
     * Parses the validator options given in @validate annotations.
     *
     * @param string $validateValue
     * @return array
     */
    public function parseValidatorAnnotation($validateValue) {
        return parent::parseValidatorAnnotation($validateValue);
    }

    /**
	 * We need to override this method to get the validate annotations also from TypoScript Configuration
	 *
	 * @param string $className
	 * @param string $methodName
	 *
	 * @return array
	 */
	public function getMethodValidateAnnotations($className, $methodName) {
        $validateAnnotations = parent::getMethodValidateAnnotations($className, $methodName);
        $methodParameters = $this->reflectionService->getMethodParameters($className,$methodName);
        foreach ($methodParameters as $argumentName => $methodParameterValue) {
            if (isset($this->settings[$className][$methodName][$argumentName])) {
                $overwriteDefaultValidation = isset($this->settings[$className][$methodName][$argumentName]['overwriteDefaultValidation']) ? (Boolean)$this->settings[$className][$methodName][$argumentName]['overwriteDefaultValidation'] : FALSE;
                array_push($validateAnnotations, array(
                    'argumentName' => $argumentName,
                    'validatorName' => 'Portrino\\PxValidation\\Domain\\Validator\\TypoScriptValidator',
                    'validatorOptions' => array(
                        'className' => $className,
                        'methodName' => $methodName,
                        'argumentName' => $argumentName,
                        'overwriteDefaultValidation' => $overwriteDefaultValidation
                    )
                ));
            }
        }
        
		return $validateAnnotations;
	}
}
