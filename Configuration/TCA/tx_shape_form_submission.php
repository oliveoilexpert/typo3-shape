<?php

use UBOS\Shape\Utility\TcaUtility as Util;

$ctrl = [
	'label' => 'tstamp',
	'title' => Util::t('form_submission.ctrl.title'),
	'tstamp' => 'tstamp',
	'crdate' => 'crdate',
	'origUid' => 't3_origuid',
	'sortby' => 'sorting',
	'delete' => 'deleted',
	'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/svgs/content/content-elements-mailform.svg',
	'enablecolumns' => [
		'disabled' => 'hidden',
	],
	'searchFields' => 'title',
	'security' => [
		'ignorePageTypeRestriction' => true,
	],
];
$interface = [];
$columns = [
	'form' => [
		'config' => [
			'type' => 'group',
			'allowed' => 'tx_shape_form',
			'size' => 1,
			'maxitems' => 1,
		],
	],
	'plugin' => [
		'config' => [
			'type' => 'group',
			'allowed' => 'tt_content',
			'size' => 1,
			'maxitems' => 1,
		],
	],
	'form_values' => [
		'config' => [
			'type' => 'json',
		],
	],
	'tstamp' => [
		'exclude' => true,
		'config' => [
			'type' => 'datetime',
			'readOnly' => true,
		],
	],
];
foreach ($columns as $key => $column) {
	$columns[$key]['label'] = Util::t('form_submission.' . $key);
}
$palettes = [];
$showItem = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
    	tstamp,
        form, 
        form_values,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, 
        hidden';

return [
	'ctrl' => $ctrl,
	'interface' => $interface,
	'columns' => $columns,
	'palettes' => $palettes,
	'types' => [
		'0' => [
			'showitem' => $showItem,
		],
	],
];
