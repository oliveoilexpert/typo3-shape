<?php
defined('TYPO3') or die();

$GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths'][573765204] = 'EXT:shape/Resources/Private/Templates/Finisher/Mail/';

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'shape',
	'form',
	[
		\UBOS\Shape\Controller\FormController::class => 'form, formStep, formSubmit'
	],
	[
		\UBOS\Shape\Controller\FormController::class => 'formStep, formSubmit'
	],
	'CType'
);