<?php
defined('TYPO3_MODE') || die();

(function () {

    /**
     * Temporary variables
     */
    $extensionKey = 'px_validation';

    /**
     * Default TypoScript for SitePackage
     */
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
        $extensionKey,
        'Configuration/TypoScript',
        'PxValidation'
    );

})();
