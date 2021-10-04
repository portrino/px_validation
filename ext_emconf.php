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
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-10.4.99',
        ],
        'conflicts' => [],
        'suggests' => [
        ],
    ]
];
