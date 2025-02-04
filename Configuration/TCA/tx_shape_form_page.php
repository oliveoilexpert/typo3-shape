<?php

use UBOS\Shape\Utility\TcaUtility as Util;

$ctrl = [
	'label' => 'title',
	'label_alt_force' => true,
	'title' => Util::t('form_page.ctrl.title'),
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
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
		],
	],
	'type' => [
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
		'config' => [
			'type' => 'select',
			'foreign_table' => 'tx_shape_form',
			'minitems' => 0,
			'maxitems' => 1,
		],
	],
	'fields' => [
		'displayCond' => 'FIELD:type:!=:summary',
		'config' => [
			'type' => 'inline',
			'allowed' => 'tx_shape_field',
			'foreign_table' => 'tx_shape_field',
			'MM' => 'tx_shape_page_field_mm',
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
		'config' => [
			'type' => 'input',
			'size' => 40,
		],
	],
	'prev_label' => [
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
		],
	],
	'next_label' => [
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
		],
	],
];
foreach ($columns as $key => $column) {
	$columns[$key]['label'] = Util::t('form_page.' . $key);
}
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
