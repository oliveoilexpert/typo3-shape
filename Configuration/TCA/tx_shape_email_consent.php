<?php

use UBOS\Shape\Utility\TcaUtility as Util;

$ctrl = [
	'label' => 'form',
	'label_alt' => 'email, tstamp',
	'label_alt_force' => 1,
	'title' => Util::t('email_consent.ctrl.title'),
	'tstamp' => 'tstamp',
	'crdate' => 'crdate',
	'sortby' => 'sorting',
	'delete' => 'deleted',
	'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/svgs/actions/actions-check-circle.svg',
	'enablecolumns' => [
		'disabled' => 'hidden',
	],
	'searchFields' => 'email',
	'security' => [
		'ignorePageTypeRestriction' => true,
	],
];
$interface = [];
$columns = [
	'state' => [
		'config' => [
			'type' => 'input',
			'readOnly' => true,
		]
	],
	'session' => [
		'config' => [
			'type' => 'text',
			'readOnly' => true,
		],
	],
	'form' => [
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'foreign_table' => 'tx_shape_form',
			'size' => 1,
			'maxitems' => 1,
			'hideSuggest' => true,
			'fieldWizard' => [
				'tableList' => [
					'disabled' => true,
				]
			],
			'readOnly' => true,
		],
	],
	'plugin_uid' => [
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'foreign_table' => 'tt_content',
			'size' => 1,
			'maxitems' => 1,
			'hideSuggest' => true,
			'foreign_table_where' => 'AND {#tt_content}.{#CType}=\'shape_form\'',
			'fieldWizard' => [
				'tableList' => [
					'disabled' => true,
				]
			],
			'readOnly' => true,
		],
	],
	'plugin_pid' => [
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'foreign_table' => 'pages',
			'size' => 1,
			'maxitems' => 1,
			'hideSuggest' => true,
			'fieldWizard' => [
				'tableList' => [
					'disabled' => true,
				]
			],
			'readOnly' => true,
		],
	],
	'email' => [
		'config' => [
			'type' => 'input',
			'readOnly' => true,
		],
	],
	'valid_until' => [
		'config' => [
			'type' => 'datetime',
			'readOnly' => true,

		],
	],
	'tstamp' => [
		'config' => [
			'type' => 'datetime',
			'readOnly' => true,

		],
	],
];
foreach ($columns as $key => $column) {
	$columns[$key]['label'] = Util::t('form_submission.' . $key);
}
$palettes = [
	'general' => [
		'showitem' => '
		tstamp, valid_until,
		--linebreak--,
		state, email,
		--linebreak--,
		plugin_pid, plugin_uid,
		--linebreak--,
		form,
		--linebreak--,
		session,',
	],
];
$showItem = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
    	--palette--;;general,
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
