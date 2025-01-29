<?php

$ctrl = [
	'label' => 'title',
	'title' => 'Form field validation',
	'tstamp' => 'tstamp',
	'crdate' => 'crdate',
	'origUid' => 't3_origuid',
	'sortby' => 'sorting',
	'delete' => 'deleted',
	'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/svgs/form/form-validator.svg',
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
			'type' => 'text',
			'renderType' => 'codeEditor',
			'format' => 'typoscript',
		],
	],
	'field_parents' => [
		'label' => 'Used in fields',
		'config' => [
			'type' => 'group',
			'allowed' => 'tx_shape_field',
			'MM' => 'tx_shape_field_validation_mm',
			'MM_opposite_field' => 'validation',
		],
	],
];
$palettes = [
	'base' => [
		'showitem' => 'title, field_parents, --linebreak--, validators, --linebreak--, options',
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
