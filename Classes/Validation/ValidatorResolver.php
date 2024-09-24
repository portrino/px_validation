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

use Doctrine\Common\Annotations\DocParser;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * Class ValidatorResolver
 */
class ValidatorResolver extends \TYPO3\CMS\Extbase\Validation\ValidatorResolver
{
    /**
     * Contains the settings of the current extension
     *
     * @var array
     */
    protected $settings = [];

    /**
     * @var ConfigurationManagerInterface
     */
    protected $configurationManager;

    /**
     * @param ConfigurationManagerInterface $configurationManager
     */
    public function injectConfigurationManager(ConfigurationManagerInterface $configurationManager): void
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
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
     */
    public function parseValidatorAnnotation(string $validateValue, array $validatorOptions = []): array
    {
        $context = '';
        if (array_key_exists('className', $validatorOptions) && array_key_exists('argumentName', $validatorOptions)) {
            $context = 'property ' . $validatorOptions['className'] . '::$' . $validatorOptions['argumentName'];
        }
        return (new DocParser())->parse($validateValue, $context);
    }
}
