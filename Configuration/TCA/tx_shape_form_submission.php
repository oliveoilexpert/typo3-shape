<?php

use UBOS\Shape\Utility\TcaUtility as Util;

$ctrl = [
	'label' => 'form',
	'label_alt' => 'tstamp',
	'label_alt_force' => 1,
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
			//'readOnly' => true,
			'hideSuggest' => true,
			'fieldWizard' => [
				'tableList' => [
					'disabled' => true,
				]
			]
		],
	],
	'plugin' => [
		'config' => [
			'type' => 'group',
			'allowed' => 'tt_content',
			'size' => 1,
			'maxitems' => 1,
			//'readOnly' => true,
			'hideSuggest' => true,
			'fieldWizard' => [
				'tableList' => [
					'disabled' => true,
				]
			]
		],
	],
	'fe_user' => [
		'config' => [
			'type' => 'group',
			'allowed' => 'fe_users',
			'size' => 1,
			'maxitems' => 1,
			//'readOnly' => true,
			'hideSuggest' => true,
			'fieldWizard' => [
				'tableList' => [
					'disabled' => true,
				]
			]
		],
	],
	'form_values' => [
		'config' => [
			'type' => 'json',
			'readOnly' => true,
		],
	],
	'tstamp' => [
		'exclude' => true,
		'config' => [
			'type' => 'datetime',
			'readOnly' => true,

		],
	],
	'user_ip' => [
		'config' => [
			'type' => 'input',
			'max' => 15,
			'eval' => 'trim',
			'readOnly' => true,
		],
	],
	'user_agent' => [
		'config' => [
			'type' => 'text',
			'eval' => 'trim',
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
		tstamp,
		--linebreak--,
		form, plugin,
		--linebreak--,
		fe_user,
		--linebreak--,
		form_values,
		--linebreak--,
		user_agent, user_ip,',
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
