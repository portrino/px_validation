<?php

defined('TYPO3') || die();

call_user_func(static function () {

    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('tt_address')) {

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            'PxValidation',
            'Demo',
            [
                \Portrino\PxValidation\Controller\DemoController::class => 'new, create, finish',
            ],
            [
                \Portrino\PxValidation\Controller\DemoController::class => 'create, finish',
            ]
        );

    }

    // Transparent, controller-agnostic injection of TypoScript driven validators:
    // the custom ReflectionService builds a ClassSchema that augments controller
    // action arguments with a TypoScriptValidator (additive validation).
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Extbase\Reflection\ReflectionService::class] = [
        'className' => \Portrino\PxValidation\Reflection\ReflectionService::class,
    ];

    // Agnostic suppression of built-in (attribute-based) validators per model class,
    // configured via plugin.tx_pxvalidation.settings.suppressBaseValidation.<ModelFQCN>.
    // This replaces the former per-action "overwriteDefaultValidation" flag without
    // requiring any controller code (no trait).
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Extbase\Validation\ValidatorResolver::class] = [
        'className' => \Portrino\PxValidation\Validation\ValidatorResolver::class,
    ];
});
