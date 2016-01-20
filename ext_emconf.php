<?php

########################################################################
# Extension Manager/Repository config file for ext: "px_validation"
#
#
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Extbase Validation via Typoscript',
	'description' => 'Provides Extbase Validation via TypoScript.',
	'category' => 'fe',
	'author' => 'André Wuttig',
	'author_email' => 'wuttig@portrino.de',
	'author_company' => 'portrino GmbH',
	'state' => 'beta',
	'uploadfolder' => '0',
	'createDirs' => '',
	'clearCacheOnLoad' => 0,
	'version' => '1.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '6.2.0-7.99.99',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
);

?>