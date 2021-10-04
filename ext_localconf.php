<?php
defined('TYPO3_MODE') || die();

(function () {
    $extbaseObjectContainer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Object\Container\Container::class);
    $extbaseObjectContainer->registerImplementation(
        \TYPO3\CMS\Extbase\Reflection\ClassSchema::class,
        \Portrino\PxValidation\Reflection\ClassSchema::class
    );
    $extbaseObjectContainer->registerImplementation(
        \TYPO3\CMS\Extbase\Reflection\ReflectionService::class,
        \Portrino\PxValidation\Reflection\ReflectionService::class
    );
})();
