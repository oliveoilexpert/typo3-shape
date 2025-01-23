<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;

defined('TYPO3') or die();


$pluginLabel = 'Shape form';
$pluginDescription = 'Plugin to render a shape form.';
$pluginGroup = 'forms';
$pluginIcon = 'content-form';

$pluginKey = ExtensionUtility::registerPlugin(
	'Shape',
	'Form',
	$pluginLabel,
	$pluginIcon,
	$pluginGroup,
	$pluginDescription,
);
ExtensionManagementUtility::addPlugin(
	[
		'label' => $pluginLabel,
		'description' => $pluginDescription,
		'group' => $pluginGroup,
		'value' => $pluginKey,
		'icon' => $pluginIcon,
	],
	'CType',
	'shape',
);

$GLOBALS['TCA']['tt_content']['columns']['pi_flexform']['config']['ds']['*' . ',' . $pluginKey] = 'FILE:EXT:shape/Configuration/FlexForms/Form.xml';

$GLOBALS['TCA']['tt_content']['types'][$pluginKey] = [
	'showitem' => '	--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
		--palette--;;general,
		pi_flexform,',
	'columnsOverrides' => []
];


//$GLOBALS['TCA']['tt_content']['types'][$pluginKey]['previewRenderer'] =