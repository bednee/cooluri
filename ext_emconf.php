<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'CoolUri',
	'description' => 'RealURL alternative. Have nice URLs instead of ugly with parameters. CoolUri has user-friendly XML configuration file. For simple setup, just use the one supplied with extension and you are ready to go.',
	'version' => '1.2.2',
	'state' => 'stable',
	'author' => 'Jan Bednarik',
	'author_email' => 'info@bednarik.org',
	'constraints' => array(
		'depends' => array(
			'typo3' => '9.5.0-9.9.99',
            'typo3db_legacy' => '1.1.1-1.1.99'
		),
	),
);
