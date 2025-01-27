<?php

$ctrl = [
	'label' => 'label',
	'title' => 'Form field datalist',
	'tstamp' => 'tstamp',
	'crdate' => 'crdate',
	'origUid' => 't3_origuid',
	'sortby' => 'sorting',
	'delete' => 'deleted',
	'versioningWS' => true,
	'languageField' => 'sys_language_uid',
	'transOrigPointerField' => 'l10n_parent',
	'transOrigDiffSourceField' => 'l10n_diffsource',
	'iconfile' => 'EXT:shape/Resources/Public/Icons/form-option.svg',
	'enablecolumns' => [
		'disabled' => 'hidden',
	],
	'searchFields' => 'label',
];
$interface = [];
$columns = [
	'label' => [
		'label' => 'Label',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
		],
	],
	'list' => [
		'label' => 'List',
		'config' => [
			'type' => 'text',
			'eval' => 'trim',
			'rows' => 10,
			'fixedFont' => true,
		],
	],
	'strict' => [
		'label' => 'Strict',
		'description' => 'Only allow values from the list',
		'config' => [
			'type' => 'check',
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
		'showitem' => 'label, strict, --linebreak--, list',
	],
];
$showItem = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
        --palette--;;base,
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, 
        sys_language_uid, 
        l10n_parent, 
        l10n_diffsource, 
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
