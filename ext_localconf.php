<?php

use UBOS\Shape\Controller;

defined('TYPO3') or die();

$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['shape'] = ['UBOS\Shape\ViewHelpers'];
$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['tx_shape_input_field'] = 'EXT:shape/Configuration/RTE/InputField.yaml';

$GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['shape'] = [
	'finishers' => [
		'sendEmail' => [
			'templates' => [
				'Finisher/SendEmail/Default' => [
					'label' => 'Default',
					'format' => \TYPO3\CMS\Core\Mail\FluidEmail::FORMAT_BOTH,
				],
			],
		]
	]
];

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Shape',
	'Form',
	[Controller\FormController::class => 'render, run, finished'],
	[Controller\FormController::class => 'run, finished'],
	'CType'
);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Shape',
	'Consent',
	[Controller\ConsentController::class => 'approve'],
	[Controller\ConsentController::class => 'approve'],
	'CType'
);