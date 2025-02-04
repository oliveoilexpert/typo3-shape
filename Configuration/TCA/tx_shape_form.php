<?php

use UBOS\Shape\Utility\TcaUtility as Util;

$ctrl = [
	'label' => 'title',
	'title' => Util::t('form.ctrl.title'),
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
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
			'required' => true,
		],
	],
	'name' => [
		'config' => [
			'type' => 'slug',
			'generatorOptions' => [
				'fields' => ['title'],
				'fieldSeparator' => '-',
				'replacements' => [ '/' => '' ],
			],
			'appearance' => [
				'prefix' => \UBOS\Shape\UserFunctions\Tca::class . '->getEmptySlugPrefix',
			],
			'fallbackCharacter' => '-',
			'eval' => 'uniqueInSite',
			'default' => '',
		],
	],
	'pages' => [
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
foreach ($columns as $key => $column) {
	$columns[$key]['label'] = Util::t('form.' . $key);
}

$palettes = [
	'base' => [
		'showitem' => 'title, name',
	],
];
$showItem = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
        --palette--;;base, 
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
