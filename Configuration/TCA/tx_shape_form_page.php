<?php

$ctrl = [
	'label' => 'title',
	'label_alt_force' => true,
	'title' => 'Form page',
	'tstamp' => 'tstamp',
	'crdate' => 'crdate',
	'origUid' => 't3_origuid',
	'sortby' => 'sorting',
	'delete' => 'deleted',
	'versioningWS' => true,
	'languageField' => 'sys_language_uid',
	'transOrigPointerField' => 'l10n_parent',
	'transOrigDiffSourceField' => 'l10n_diffsource',
	'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/svgs/form/form-page.svg',
	'enablecolumns' => [
		'disabled' => 'hidden',
	],
	'searchFields' => 'title',
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
	'type' => [
		'label' => 'Type',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => [
				['Page', 'page'],
				['Summary', 'summary'],
			],
		],
	],
	'form_parent' => [
		'label' => 'Form',
		'config' => [
			'type' => 'select',
			'foreign_table' => 'tx_shape_form',
			'minitems' => 0,
			'maxitems' => 1,
		],
	],
	'fields' => [
		'label' => 'Fields',
		'config' => [
			'type' => 'group',
			'MM' => 'tx_shape_page_field_mm',
			'foreign_table' => 'tx_shape_field',
			'allowed' => 'tx_shape_field',
			'fieldControl' => [
				'editPopup' => [
					'disabled' => false,
				],
				'addRecord' => [
					'disabled' => false,
				],
			],
			'fieldWizard' => [
				'recordsOverview' => [
					'disabled' => false,
				],
				'tableList' => [
					'disabled' => true,
				],
			]
		],
	],
	'display_condition' => [
		'label' => 'Display condition',
		'config' => [
			'type' => 'input',
			'size' => 40,
		],
	],
	'prev_label' => [
		'label' => 'Previous button label',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
		],
	],
	'next_label' => [
		'label' => 'Next button label',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
		],
	],
];
$palettes = [
	'title' => [
		'showitem' => 'title, type',
	],
	'button-labels' => [
		'showitem' => 'prev_label, next_label',
	]
];
$showItem = '
	--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
		--palette--;;title,
		fields,
		--palette--;;button-labels,
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
			'showitem' => $showItem
		],
	],
];
