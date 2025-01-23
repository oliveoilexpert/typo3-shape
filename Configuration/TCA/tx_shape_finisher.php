<?php

$ctrl = [
	'label' => 'type',
	'title' => 'Form finisher',
	'tstamp' => 'tstamp',
	'crdate' => 'crdate',
	'origUid' => 't3_origuid',
	'sortby' => 'sorting',
	'delete' => 'deleted',
	'versioningWS' => true,
	'languageField' => 'sys_language_uid',
	'transOrigPointerField' => 'l10n_parent',
	'transOrigDiffSourceField' => 'l10n_diffsource',
	'iconfile' => 'EXT:core/Resources/Public/Icons/T3Icons/svgs/form/form-finisher.svg',
	'enablecolumns' => [
		'disabled' => 'hidden',
	],
	'searchFields' => 'title',
	'security' => [
		'ignorePageTypeRestriction' => true,
	],
	'type' => 'type'
];
$interface = [];
$columns = [
	'content_parent' => [
		'label' => 'Content element parent',
		'config' => [
			'type' => 'select',
			'foreign_table' => 'tt_content',
			'size' => 1,
			'maxitems' => 1
		],
	],
	'type' => [
		'label' => 'Type',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => \UBOS\Shape\Utility\TcaUtility::selectItemsHelper([
				['', ''],
				['Save submission to database', 'UBOS\Shape\Domain\Finisher\SaveSubmissionFinisher'],
				['Mail consent process', 'UBOS\Shape\Domain\Finisher\ConsentFinisher'],
				['Save to any table', 'UBOS\Shape\Domain\Finisher\SaveToAnyTableFinisher'],
				['Send email', 'UBOS\Shape\Domain\Finisher\SendEmailFinisher'],
				['Show content elements', 'UBOS\Shape\Domain\Finisher\ShowContentElementsFinisher'],
				['Redirect', 'UBOS\Shape\Domain\Finisher\RedirectFinisher'],
			]),
		],
	],
	'condition' => [
		'label' => 'Condition',
		'description' => 'Condition in Symfony Expression Language.',
		'config' => [
			'type' => 'input',
			'size' => 100,
			'valuePicker' => [
				'items' => [
					['Field value is true / not empty', 'value("field-id")'],
					['Field value is equal to', 'value("field-id") == "some-value"'],
					['Consent was approved', 'isConsentApproved()'],
					['Consent was dismissed', 'isConsentDismissed()'],
				],
			],
		],
	],
	'settings' => [
		'label' => 'Settings',
		'displayCond' => 'FIELD:type:REQ:true',
		'config' => [
			'type' => 'flex',
			'ds' => [
				'default' => 'FILE:EXT:puck/Configuration/FlexForms/Finisher/Default.xml',
				'UBOS\Shape\Domain\Finisher\ConsentFinisher' => 'FILE:EXT:puck/Configuration/FlexForms/Finisher/ConsentFinisher.xml',
				'UBOS\Shape\Domain\Finisher\SaveSubmissionFinisher' => 'FILE:EXT:puck/Configuration/FlexForms/Finisher/SaveSubmissionFinisher.xml',
				'UBOS\Shape\Domain\Finisher\SaveToAnyTableFinisher' => 'FILE:EXT:puck/Configuration/FlexForms/Finisher/SaveToAnyTableFinisher.xml',
				'UBOS\Shape\Domain\Finisher\SendEmailFinisher' => 'FILE:EXT:puck/Configuration/FlexForms/Finisher/SendEmailFinisher.xml',
				'UBOS\Shape\Domain\Finisher\RedirectFinisher' => 'FILE:EXT:puck/Configuration/FlexForms/Finisher/RedirectFinisher.xml',
				'UBOS\Shape\Domain\Finisher\ShowContentElementsFinisher' => 'FILE:EXT:puck/Configuration/FlexForms/Finisher/ShowContentElementsFinisher.xml',
			],
			'ds_pointerField' => 'type',
		],
	],
];
$palettes = [
	'base' => [
		'showitem' => 'type, --linebreak--, condition',
	],
];
$showItem = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
		--palette--;;base,
        settings,';

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
