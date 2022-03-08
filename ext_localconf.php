<?php
defined('TYPO3_MODE') || die();

(function () {
    /**
     * Register XClasses
     */
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Extbase\Reflection\ClassSchema::class] = [
        'className' => \Portrino\PxValidation\Reflection\ClassSchema::class
    ];
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Extbase\Reflection\ReflectionService::class] = [
        'className' => \Portrino\PxValidation\Reflection\ReflectionService::class
    ];
})();
