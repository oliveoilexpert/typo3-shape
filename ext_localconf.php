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

if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('scheduler')) {
	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks'][\TYPO3\CMS\Scheduler\Task\TableGarbageCollectionTask::class]['options']['tables']['tx_shape_email_consent'] = [
		'expireField' => 'valid_until',
	];
}

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
	[Controller\ConsentController::class => 'consent'],
	[Controller\ConsentController::class => 'consent'],
	'CType'
);