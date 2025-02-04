<?php

use UBOS\Shape\Utility\TcaUtility as Util;

$columns = [
	'label' => [
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
				'fields' => ['label'],
				'fieldSeparator' => '-',
				'replacements' => [ '/' => '' ],
			],
			'appearance' => [
				'prefix' => \UBOS\Shape\UserFunctions\Tca::class.'->getEmptySlugPrefix',
			],
			'fallbackCharacter' => '-',
			'eval' => 'uniqueInPid',
			'default' => '',
		],
	],
	'description' => [
		'config' => [
			'type' => 'text',
			'rows' => 2,
		],
	],
	'placeholder' => [
		'config' => [
			'type' => 'text',
			'rows' => 2,
		],
	],
	'type' => [
		'onChange' => 'reload',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => Util::selectItemsHelper([

				[Util::t('field.type.item.text'), 'text', 'form-text', 'text-inputs'],
				[Util::t('field.type.item.textarea'), 'textarea', 'form-textarea', 'text-inputs'],
				[Util::t('field.type.item.email'), 'email', 'form-email', 'text-inputs'],
				[Util::t('field.type.item.number'), 'number', 'form-number', 'text-inputs'],
				[Util::t('field.type.item.tel'), 'tel', 'form-telephone', 'text-inputs'],
				[Util::t('field.type.item.password'), 'password', 'form-password', 'text-inputs'],
				[Util::t('field.type.item.search'), 'search', 'form-text', 'text-inputs'],
				[Util::t('field.type.item.url'), 'url', 'form-url', 'text-inputs'],

				[Util::t('field.type.item.checkbox'), 'checkbox', 'form-checkbox', 'select-inputs'],
				[Util::t('field.type.item.select'), 'select', 'form-single-select', 'select-inputs'],
				[Util::t('field.type.item.radio'), 'radio', 'form-radio-button', 'select-inputs'],
				[Util::t('field.type.item.multi-checkbox'), 'multi-checkbox', 'form-multi-checkbox', 'select-inputs'],
				[Util::t('field.type.item.multi-select'), 'multi-select', 'form-multi-select', 'select-inputs'],
				[Util::t('field.type.item.country-select'), 'country-select', 'form-single-select', 'select-inputs'],

				[Util::t('field.type.item.date'), 'date', 'form-date-picker', 'datetime'],
				[Util::t('field.type.item.datetime-local'), 'datetime-local', 'form-date-picker', 'datetime'],
				[Util::t('field.type.item.time'), 'time', 'form-date-picker', 'datetime'],
				[Util::t('field.type.item.month'), 'month', 'form-date-picker', 'datetime'],
				[Util::t('field.type.item.week'), 'week', 'form-date-picker', 'datetime'],

				[Util::t('field.type.item.file'), 'file', 'form-file-upload', 'special'],
				[Util::t('field.type.item.range'), 'range', 'form-text', 'special'],
				[Util::t('field.type.item.color'), 'color', 'form-text', 'special'],
				[Util::t('field.type.item.reset'), 'reset', 'form-text', 'special'],
				[Util::t('field.type.item.captcha'), 'captcha', 'form-text', 'special'],
				[Util::t('field.type.item.hidden'), 'hidden', 'form-hidden', 'special'],

				[Util::t('field.type.item.repeatable-container'), 'repeatable-container', 'content-container', 'container'],

				[Util::t('field.type.item.content-header'), 'content-header', 'content-header', 'no-input'],
				[Util::t('field.type.item.content-rte'), 'content-rte', 'form-static-text', 'no-input'],
				[Util::t('field.type.item.content-element'), 'content-element', 'form-content-element', 'no-input'],
			]),
			'itemGroups' => [
				'text-inputs' => Util::t('field.type.itemGroup.text-inputs'),
				'select-inputs' => Util::t('field.type.itemGroup.select-inputs'),
				'datetime-inputs' => Util::t('field.type.itemGroup.datetime-inputs'),
				'special' => Util::t('field.type.itemGroup.special'),
				'container' => Util::t('field.type.itemGroup.container'),
				'no-input' => Util::t('field.type.itemGroup.no-input'),
			],
//			'fieldWizard' => [
//				'selectIcons' => [
//					'disabled' => false,
//				],
//			],
		],
	],
	'default_value' => [
		'config' => [
			'type' => 'input',
			'size' => 30,
			'default' => null,
		],
	],
	'page_parents' => [
		'config' => [
			'type' => 'group',
			'allowed' => 'tx_shape_form_page',
			'MM' => 'tx_shape_page_field_mm',
			'foreign_table' => 'tx_shape_form_page',
			'MM_opposite_field' => 'fields',
			'size' => 1,
			'fieldWizard' => [
				'tableList' => [
					'disabled' => true,
				],
			]
		],
	],
	'field_parent' => [
		'config' => [
			'type' => 'select',
			'foreign_table' => 'tx_shape_field',
			'minitems' => 0,
			'maxitems' => 1,
		],
	],
	'fields' => [
		'config' => [
			'type' => 'inline',
			'foreign_table' => 'tx_shape_field',
			'foreign_field' => 'field_parent',
			'foreign_sortby' => 'sorting',
			'appearance' => [
				'expandSingle' => true,
				'useSortable' => true
			],
			'overrideChildTca' => [
				'columns' => [
					'page_parents' => [
						'displayCond' => 'FIELD:type:=:666666',
					]
				]
			]
		],
	],
	'required' => [
		'config' => [
			'type' => 'check',
		],
	],
	'field_options' => [
		'config' => [
			'type' => 'inline',
			'foreign_table' => 'tx_shape_field_option',
			'foreign_field' => 'field_parent',
			'foreign_sortby' => 'sorting',
			'appearance' => [
				'expandSingle' => true,
				'useSortable' => true
			],
		],
	],
	'layout' => [
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => Util::selectItemsHelper([
				['Default', 'default'],
			]),
		],
	],
	'label_layout' => [
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => Util::selectItemsHelper([
				['Default', 'default'],
				['Hidden', 'hidden'],
			]),
		],
	],
	'width' => [
		'config' => [
			'type' => 'number',
			'format' => 'integer',
			'default' => 100,
			'size' => 30,
			'range' => [
				'lower' => 20,
				'upper' => 100
			],
			'valuePicker' => [
				'items' => [
					['20', 20],
					['25', 25],
					['33', 33],
					['50', 50],
					['66', 66],
					['75', 75],
					['100', 100],
				],
			],
		],
	],
	'css_class' => [
		'config' => [
			'type' => 'input',
			'size' => 30,
		],
	],
	'validation_message' => [
		'config' => [
			'type' => 'input',
			'size' => 30,
		],
	],
	'rte_label' => [
		'config' => [
			'type' => 'text',
			'rows' => 1,
			'max' => 255,
			'enableRichtext' => true,
			'richtextConfiguration' => 'tx_shape_input_field',
		]
	],
	'disabled' => [
		'config' => [
			'type' => 'check',
		],
	],
	'readonly' => [
		'config' => [
			'type' => 'check',
		],
	],
	'multiple' => [
		'config' => [
			'type' => 'check',
		],
	],
	'pattern' => [
		'config' => [
			'type' => 'input',
			'size' => 40,
		],
	],
	'accept' => [
		'config' => [
			'type' => 'input',
			'size' => 40,
		],
	],
	'maxlength' => [
		'config' => [
			'type' => 'number',
			'format' => 'integer',
			'mode' => 'useOrOverridePlaceholder',
			'nullable' => true,
			'default' => null
		],
	],
	'min' => [
		'config' => [
			'type' => 'input',
			'eval' => 'is_in',
			'is_in' => '0123456789-.',
			'nullable' => true,
			'default' => null
		],
	],
	'max' => [
		'config' => [
			'type' => 'input',
			'eval' => 'is_in',
			'is_in' => '0123456789-.',
			'nullable' => true,
			'default' => null
		],
	],
	'step' => [
		'config' => [
			'type' => 'number',
			'format' => 'decimal',
			'mode' => 'useOrOverridePlaceholder',
			'nullable' => true,
			'default' => null
		],
	],
	'datalist' => [
		'config' => [
			'type' => 'text',
			'rows' => 5,
		],
	],
	'autocomplete' => [
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'default' => '',
			'items' => Util::selectItemsHelper([
				['','','','basic'],
				['off', 'off', '', 'basic'],
				['on', 'on', '', 'basic'],

				['language', 'language', '', 'other'],
				['organization', 'organization', '', 'other'],
				['organization-title', 'organization-title', '', 'other'],
				['photo', 'photo', '', 'other'],
				['sex', 'sex', '', 'other'],
				['transaction-amount', 'transaction-amount', '', 'other'],
				['transaction-currency', 'transaction-currency', '', 'other'],
				['url', 'url', '', 'other'],

				['name', 'name', '', 'name'],
				['family-name', 'family-name', '', 'name'],
				['given-name', 'given-name', '', 'name'],
				['additional-name', 'additional-name', '', 'name'],
				['nickname', 'nickname', '', 'name'],
				['honoric-prefix', 'honoric-prefix', '', 'name'],
				['honoric-suffix', 'honoric-suffix', '', 'name'],
				['username', 'username', '', 'name'],

				['street-address', 'street-address', '', 'address'],
				['postal-code', 'postal-code', '', 'address'],
				['country', 'country', '', 'address'],
				['country-name', 'country-name', '', 'address'],
				['address-level1', 'address-level1', '', 'address'],
				['address-level2', 'address-level2', '', 'address'],
				['address-level3', 'address-level3', '', 'address'],
				['address-level4', 'address-level4', '', 'address'],
				['address-line1', 'address-line1', '', 'address'],
				['address-line2', 'address-line2', '', 'address'],
				['address-line3', 'address-line3', '', 'address'],

				['bday', 'bday', '', 'birthday'],
				['bday-day', 'bday-day', '', 'birthday'],
				['bday-month', 'bday-month', '', 'birthday'],
				['bday-year', 'bday-year', '', 'birthday'],

				['email', 'email', '', 'digital-contact'],
				['tel', 'tel', '', 'digital-contact'],
				['tel-area-code', 'tel-area-code', '', 'digital-contact'],
				['tel-country-code', 'tel-country-code', '', 'digital-contact'],
				['tel-extension', 'tel-extension', '', 'digital-contact'],
				['tel-local', 'tel-local', '', 'digital-contact'],
				['tel-local-prefix', 'tel-local-prefix', '', 'digital-contact'],
				['tel-local-suffix', 'tel-local-suffix', '', 'digital-contact'],
				['tel-national', 'tel-national', '', 'digital-contact'],
				['impp', 'impp', '', 'digital-contact'],

				['cc-name', 'cc-name', '', 'credit-card'],
				['cc-family-name', 'cc-family-name', '', 'credit-card'],
				['cc-given-name', 'cc-given-name', '', 'credit-card'],
				['cc-additional-name', 'cc-additional-name', '', 'credit-card'],
				['cc-csc', 'cc-csc', '', 'credit-card'],
				['cc-exp', 'cc-exp', '', 'credit-card'],
				['cc-exp-month', 'cc-exp-month', '', 'credit-card'],
				['cc-exp-year', 'cc-exp-year', '', 'credit-card'],
				['cc-number', 'cc-number', '', 'credit-card'],
				['cc-type', 'cc-type', '', 'credit-card'],

				['current-password', 'current-password', '', 'password'],
				['new-password', 'new-password', '', 'password'],
				['one-time-code', 'one-time-code', '', 'password'],
			]),
			'itemGroups' => [
				'basic' => Util::t('field.autocomplete.basic'),
				'name' => Util::t('field.autocomplete.name'),
				'address' => Util::t('field.autocomplete.address'),
				'digital-contact' => Util::t('field.autocomplete.digital-contact'),
				'birthday' => Util::t('field.autocomplete.birthday'),
				'credit-card' => Util::t('field.autocomplete.credit-card'),
				'password' => Util::t('field.autocomplete.password'),
				'other' => Util::t('field.autocomplete.other'),

			]
		],
	],
	'autocomplete_modifier' => [
		'label' => 'Autocomplete modifier',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'default' => '',
			'items' => Util::selectItemsHelper([
				['', ''],
				['shipping', 'shipping ', '', 'address-group'],
				['billing', 'billing ', '', 'address-group'],

				['home', 'home ', '', 'contact-type'],
				['work', 'work ', '', 'contact-type'],
				['mobile', 'mobile ', '', 'contact-type'],
				['fax', 'fax ', '', 'contact-type'],
				['page', 'page ', '', 'contact-type'],
			]),
			'itemGroups' => [
				'address-group' => Util::t('field.autocomplete_modifier.address-group'),
				'contact-type' => Util::t('field.autocomplete_modifier.contact-type'),
			]
		]
	],
	'display_condition' => [
		'description' => Util::t('field.display_condition.description'),
		'config' => [
			'type' => 'input',
			'size' => 100,
			'valuePicker' => [
				'items' => [
					['Field value is true / not empty', 'value("field-id")'],
					['Field value is equal to', 'value("field-id") == "some-value"'],
				],
			],
		],
	],
	'js_display_condition' => [
		'description' => Util::t('field.js_display_condition.description'),
		'config' => [
			'type' => 'input',
			'size' => 100,
			'valuePicker' => [
				'items' => [
					['Field value is true / not empty', 'value("field-id")'],
					['Field value is equal to', 'value("field-id") == "some-value"'],
				],
			],
		],
	],
];

foreach ($columns as $key => $column) {
	$columns[$key]['label'] = Util::t('field.' . $key);
}

return $columns;