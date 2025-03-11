<?php

use UBOS\Shape\Utility\TcaUtility as Util;

defined('TYPO3') or die();

Util::addPluginType(
	'Form',
	label: Util::t('plugin.form'),
	icon: 'content-form',
	group: 'forms',
	description: Util::t('plugin.form.description'),
	flexForm: 'FILE:EXT:shape/Configuration/FlexForms/Form.xml',
	typeDefinition: [
		'showitem' => '	--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general,
		--palette--;;general,
		--palette--;;header,
		pi_flexform,',
		'columnsOverrides' => []
	]
);

Util::addPluginType(
	'Consent',
	label: Util::t('plugin.consent'),
	icon: 'content-form',
	group: 'forms',
	description: Util::t('plugin.consent.description'),
);
