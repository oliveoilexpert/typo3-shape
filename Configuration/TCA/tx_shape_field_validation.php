<?php

$ctrl = [
	'label' => 'title',
	'title' => 'Form field validation',
	'tstamp' => 'tstamp',
	'crdate' => 'crdate',
	'origUid' => 't3_origuid',
	'sortby' => 'sorting',
	'delete' => 'deleted',
	'iconfile' => 'EXT:shape/Resources/Public/Icons/form-option.svg',
	'enablecolumns' => [
		'disabled' => 'hidden',
	],
	'searchFields' => 'label',
];
$interface = [];
$columns = [
	'title' => [
		'label' => 'Title',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
		],
	],
	'validators' => [
		'label' => 'Validators',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectMultipleSideBySide',
			'default' => 'auto-validators',
			'items' => \UBOS\Shape\Utility\TcaUtility::selectItemsHelper([
				['Add validators based on type and attributes', 'auto-validators'],
			]),
		],
	],
	'options' => [
		'label' => 'Options',
		'config' => [
			'type' => 'json',
		],
	],
	'field_parent' => [
		'label' => 'Field parent',
		'config' => [
			'type' => 'select',
			'foreign_table' => 'tx_shape_field',
			'minitems' => 0,
			'maxitems' => 1,
		],
	],
];
$palettes = [
	'base' => [
		'showitem' => 'title, --linebreak--, validators, --linebreak--, options',
	],
];
$showItem = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
        --palette--;;base,
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
