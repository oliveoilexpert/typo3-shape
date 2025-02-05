<?php

use UBOS\Shape\Utility\TcaUtility as Util;

$ctrl = [
	'label' => 'type',
	'title' => Util::t('finisher.ctrl.title'),
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
		'config' => [
			'type' => 'select',
			'foreign_table' => 'tt_content',
			'size' => 1,
			'maxitems' => 1
		],
	],
	'type' => [
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => Util::selectItemsHelper([
				['', ''],
				[Util::t('finisher.type.item.save_submission'), 'UBOS\Shape\Domain\Finisher\SaveSubmissionFinisher'],
				[Util::t('finisher.type.item.consent'), 'UBOS\Shape\Domain\Finisher\ConsentFinisher'],
				[Util::t('finisher.type.item.save_to_any_table'), 'UBOS\Shape\Domain\Finisher\SaveToAnyTableFinisher'],
				[Util::t('finisher.type.item.send_email'), 'UBOS\Shape\Domain\Finisher\SendEmailFinisher'],
				[Util::t('finisher.type.item.show_content_elements'), 'UBOS\Shape\Domain\Finisher\ShowContentElementsFinisher'],
				[Util::t('finisher.type.item.redirect'), 'UBOS\Shape\Domain\Finisher\RedirectFinisher'],
			]),
		],
	],
	'condition' => [
		'description' => Util::t('finisher.condition.description'),
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
		'displayCond' => 'FIELD:type:REQ:true',
		'config' => [
			'type' => 'flex',
			'ds' => [
				'default' => 'FILE:EXT:shape/Configuration/FlexForms/Finisher/Default.xml',
				'UBOS\Shape\Domain\Finisher\ConsentFinisher' => 'FILE:EXT:shape/Configuration/FlexForms/Finisher/ConsentFinisher.xml',
				'UBOS\Shape\Domain\Finisher\SaveSubmissionFinisher' => 'FILE:EXT:shape/Configuration/FlexForms/Finisher/SaveSubmissionFinisher.xml',
				'UBOS\Shape\Domain\Finisher\SaveToAnyTableFinisher' => 'FILE:EXT:shape/Configuration/FlexForms/Finisher/SaveToAnyTableFinisher.xml',
				'UBOS\Shape\Domain\Finisher\SendEmailFinisher' => 'FILE:EXT:shape/Configuration/FlexForms/Finisher/SendEmailFinisher.xml',
				'UBOS\Shape\Domain\Finisher\RedirectFinisher' => 'FILE:EXT:shape/Configuration/FlexForms/Finisher/RedirectFinisher.xml',
				'UBOS\Shape\Domain\Finisher\ShowContentElementsFinisher' => 'FILE:EXT:shape/Configuration/FlexForms/Finisher/ShowContentElementsFinisher.xml',
			],
			'ds_pointerField' => 'type',
		],
	],
];
foreach ($columns as $key => $column) {
	$columns[$key]['label'] = Util::t('finisher.' . $key);
}
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
