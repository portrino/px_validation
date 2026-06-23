<?php

defined('TYPO3') || die();

call_user_func(static function () {

    if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('tt_address')) {
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'PxValidation',
            'Demo',
            'Demo',
            'EXT:px_validation/Resources/Public/Icons/Extension.svg',
        );
    }

});
