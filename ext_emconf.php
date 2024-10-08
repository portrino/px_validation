<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Extbase Validation via Typoscript',
    'description' => 'Provides Extbase Validation via TypoScript.',
    'category' => 'fe',
    'author' => 'André Wuttig, Axel Böswetter, Thomas Griessbach',
    'author_email' => 'support@portrino.de',
    'author_company' => 'portrino GmbH',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearCacheOnLoad' => true,
    'version' => '4.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.0.0-12.5.99',
        ],
        'conflicts' => [],
        'suggests' => [
        ],
    ],
];
