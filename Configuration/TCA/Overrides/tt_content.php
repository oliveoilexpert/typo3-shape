<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Extbase\Utility\ExtensionUtility;
use UBOS\Shape\Utility\TcaUtility as Util;

defined('TYPO3') or die();


$pluginLabel = Util::t('plugin.form');
$pluginDescription = Util::t('plugin.form.description');
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