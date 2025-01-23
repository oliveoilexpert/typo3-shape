<?php

$ctrl = [
	'label' => 'title',
	'title' => 'Form',
	'tstamp' => 'tstamp',
	'crdate' => 'crdate',
	'origUid' => 't3_origuid',
	'sortby' => 'sorting',
	'delete' => 'deleted',
	'versioningWS' => true,
	'languageField' => 'sys_language_uid',
	'transOrigPointerField' => 'l10n_parent',
	'transOrigDiffSourceField' => 'l10n_diffsource',
	'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/svgs/mimetypes/mimetypes-x-content-form.svg',
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
	'title' => [
		'label' => 'Title',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
			'required' => true,
		],
	],
	'pages' => [
		'label' => 'Pages',
		'config' => [
			'type' => 'inline',
			'foreign_table' => 'tx_shape_form_page',
			'foreign_field' => 'form_parent',
			'foreign_sortby' => 'sorting',
			'appearance' => [
				'expandSingle' => true,
				'useSortable' => true
			],
		],
	],
];
$palettes = [];
$showItem = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
        title, 
        pages,
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
