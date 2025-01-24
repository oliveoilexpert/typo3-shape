<?php

use UBOS\Shape\Controller\FormController;

defined('TYPO3') or die();

$GLOBALS['TYPO3_CONF_VARS']['SYS']['fluid']['namespaces']['shape'] = ['UBOS\Shape\ViewHelpers'];
$GLOBALS['TYPO3_CONF_VARS']['RTE']['Presets']['tx_shape_input_field'] = 'EXT:shape/Configuration/RTE/InputField.yaml';

$GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths'][573765204] = 'EXT:shape/Resources/Private/Templates/Finisher/Mail/';

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'Shape',
	'Form',
	[FormController::class => 'form, formStep, formSubmit'],
	[FormController::class => 'formStep, formSubmit'],
	'CType'
);