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

use Portrino\PxValidation\Domain\Validator\TypoScriptValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Reflection\Exception\UnknownClassException;
use TYPO3\CMS\Extbase\Validation\Exception\InvalidTypeHintException;
use TYPO3\CMS\Extbase\Validation\Exception\InvalidValidationConfigurationException;

/**
 * Reflection service for acquiring reflection based information.
 * Originally based on the TYPO3.Flow reflection service.
 */
class ReflectionService extends \TYPO3\CMS\Extbase\Reflection\ReflectionService
{

    /**
     * Builds class schemata from classes annotated as entities or value objects
     *
     * @param string $className
     * @return ClassSchema The class schema
     * @throws UnknownClassException
     * @throws InvalidTypeHintException
     * @throws InvalidValidationConfigurationException
     */
    protected function buildClassSchema($className): \TYPO3\CMS\Extbase\Reflection\ClassSchema
    {
        try {
            $classSchema = new \Portrino\PxValidation\Reflection\ClassSchema($className);
        } catch (\ReflectionException $e) {
            throw new UnknownClassException($e->getMessage() . '. Reflection failed.', 1278450972, $e);
        }

        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $settings = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
            'PxValidation'
        );

        // add TS validators for all methods of this class
        if (isset($settings[$className])) {
            $methodParameters = [];
            foreach ($classSchema->getRawMethods() as $methodName => $method) {
                if ($method['public'] && $method['params']) {
                    $methodParameters[$methodName] = $method['params'];
                }
            }

            foreach ($methodParameters as $methodName => $arguments) {
                foreach ($arguments as $argumentName => $argumentValue) {
                    if (isset($settings[$className][$methodName][$argumentName])) {
                        if (isset($settings[$className][$methodName][$argumentName]['overwriteDefaultValidation'])) {
                            $overwriteDefaultValidation = (bool)$settings[$className][$methodName][$argumentName]['overwriteDefaultValidation'];
                        } else {
                            $overwriteDefaultValidation = false;
                        }

                        $classSchema->addValidator(
                            $methodName,
                            $argumentName,
                            TypoScriptValidator::class,
                            [
                                'className' => $className,
                                'methodName' => $methodName,
                                'argumentName' => $argumentName,
                                'overwriteDefaultValidation' => $overwriteDefaultValidation
                            ]
                        );
                    }
                }
            }
        }

        $this->classSchemata[$className] = $classSchema;
        $this->dataCacheNeedsUpdate = true;
        return $classSchema;
    }
}
