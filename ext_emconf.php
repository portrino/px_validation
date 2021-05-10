<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Extbase Validation via Typoscript',
    'description' => 'Provides Extbase Validation via TypoScript.',
    'category' => 'fe',
    'author' => 'André Wuttig, Thomas Griessbach',
    'author_email' => 'wuttig@portrino.de, griessbach@portrino.de',
    'author_company' => 'portrino GmbH',
    'state' => 'stable',
    'uploadfolder' => false,
    'createDirs' => '',
    'clearCacheOnLoad' => true,
    'version' => '1.3.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0-10.4.99',
        ],
        'conflicts' => [],
        'suggests' => [
        ],
    ]
];
