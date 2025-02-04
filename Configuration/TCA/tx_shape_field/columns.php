<?php

use UBOS\Shape\Utility\TcaUtility;

$ctrl = require __DIR__.'/ctrl.php';
$typeIcons = $ctrl['typeicon_classes'];

return [
	'label' => [
		'label' => 'Label',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'eval' => 'trim',
			'required' => true,
		],
	],
	'name' => [
		'label' => 'Name',
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
		'label' => 'Description',
		'config' => [
			'type' => 'text',
			'rows' => 2,
		],
	],
	'placeholder' => [
		'label' => 'Placeholder',
		'config' => [
			'type' => 'text',
			'rows' => 2,
		],
	],
	'type' => [
		'label' => 'Type',
		'onChange' => 'reload',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => TcaUtility::selectItemsHelper([

				['Text', 'text', 'form-text', 'text-inputs'],
				['Textarea', 'textarea', 'form-textarea', 'text-inputs'],
				['Email', 'email', 'form-email', 'text-inputs'],
				['Number', 'number', 'form-number', 'text-inputs'],
				['Phone number', 'tel', 'form-telephone', 'text-inputs'],
				['Password', 'password', 'form-password', 'text-inputs'],
				['Search', 'search', 'form-text', 'text-inputs'],
				['URL', 'url', 'form-url', 'text-inputs'],

				['Checkbox', 'checkbox', 'form-checkbox', 'select-inputs'],
				['Select', 'select', 'form-single-select', 'select-inputs'],
				['Radio buttons', 'radio', 'form-radio-button', 'select-inputs'],
				['Multiple checkboxes', 'multi-checkbox', 'form-multi-checkbox', 'select-inputs'],
				['Multi-select', 'multi-select', 'form-multi-select', 'select-inputs'],
				['Country select', 'country-select', 'form-single-select', 'select-inputs'],

				['Date', 'date', 'form-date-picker', 'datetime'],
				['Datetime', 'datetime-local', 'form-date-picker', 'datetime'],
				['Time', 'time', 'form-date-picker', 'datetime'],
				['Month', 'month', 'form-date-picker', 'datetime'],
				['Week', 'week', 'form-date-picker', 'datetime'],

				['File', 'file', 'form-file-upload', 'special'],
				['Range', 'range', 'form-text', 'special'],
				['Color', 'color', 'form-text', 'special'],
				['Reset', 'reset', 'form-text', 'special'],
				['Captcha', 'captcha', 'form-text', 'special'],
				['Hidden input', 'hidden', 'form-hidden', 'special'],

				['Repeatable field container', 'repeatable-container', 'content-container', 'container'],

				['Header', 'content-header', 'content-header', 'no-input'],
				['Rich text content', 'content-rte', 'form-static-text', 'no-input'],
				['Content element', 'content-element', 'form-content-element', 'no-input'],
			]),
			'itemGroups' => [
				'text-inputs' => 'Text fields',
				'select-inputs' => 'Select fields',
				'datetime' => 'Date and time',
				'special' => 'Other',
				'container' => 'Field container',
				'no-input' => 'Content only / No input',
			],
//			'fieldWizard' => [
//				'selectIcons' => [
//					'disabled' => false,
//				],
//			],
		],
	],
	'default_value' => [
		'label' => 'Default value',
		'config' => [
			'type' => 'input',
			'size' => 30,
			'default' => null,
		],
	],
	'page_parents' => [
		'label' => 'On page',
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
		'label' => 'Field parent',
		'config' => [
			'type' => 'select',
			'foreign_table' => 'tx_shape_field',
			'minitems' => 0,
			'maxitems' => 1,
		],
	],
	'fields' => [
		'label' => 'Fields',
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
		'label' => 'Required',
		'config' => [
			'type' => 'check',
		],
	],
	'field_options' => [
		'label' => 'Options',
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
		'label' => 'Layout',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => TcaUtility::selectItemsHelper([
				['Default', 'default'],
			]),
		],
	],
	'label_layout' => [
		'label' => 'Label layout',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'items' => TcaUtility::selectItemsHelper([
				['Default', 'default'],
				['Hidden', 'hidden'],
			]),
		],
	],
	'width' => [
		'label' => 'Width (%)',
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
		'label' => 'CSS Class',
		'config' => [
			'type' => 'input',
			'size' => 30,
		],
	],
	'validation_message' => [
		'label' => 'Custom validation message',
		'config' => [
			'type' => 'input',
			'size' => 30,
		],
	],
	'rte_label' => [
		'label' => 'Rich text label',
		'config' => [
			'type' => 'text',
			'rows' => 1,
			'max' => 255,
			'enableRichtext' => true,
			'richtextConfiguration' => 'tx_shape_input_field',
		]
	],
	'disabled' => [
		'label' => 'Disabled',
		'config' => [
			'type' => 'check',
		],
	],
	'readonly' => [
		'label' => 'Readonly',
		'config' => [
			'type' => 'check',
		],
	],
	'multiple' => [
		'label' => 'Multiple',
		'config' => [
			'type' => 'check',
		],
	],
	'pattern' => [
		'label' => 'Regular expression pattern',
		'config' => [
			'type' => 'input',
			'size' => 40,
		],
	],
	'accept' => [
		'label' => 'Accepted MIME types',
		'config' => [
			'type' => 'input',
			'size' => 40,
		],
	],
	'maxlength' => [
		'label' => 'Maximum length',
		'config' => [
			'type' => 'number',
			'format' => 'integer',
			'mode' => 'useOrOverridePlaceholder',
			'nullable' => true,
			'default' => null
		],
	],
	'min' => [
		'label' => 'Minimum value',
		'config' => [
			'type' => 'input',
			'eval' => 'is_in',
			'is_in' => '0123456789-.',
			'nullable' => true,
			'default' => null
		],
	],
	'max' => [
		'label' => 'Maximum value',
		'config' => [
			'type' => 'input',
			'eval' => 'is_in',
			'is_in' => '0123456789-.',
			'nullable' => true,
			'default' => null
		],
	],
	'step' => [
		'label' => 'Increment step',
		'config' => [
			'type' => 'number',
			'format' => 'decimal',
			'mode' => 'useOrOverridePlaceholder',
			'nullable' => true,
			'default' => null
		],
	],
	'datalist' => [
		'label' => 'Datalist',
		'config' => [
			'type' => 'text',
			'rows' => 5,
		],
	],
	'autocomplete' => [
		'label' => 'Autocomplete',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'default' => '',
			'items' => TcaUtility::selectItemsHelper([
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
				'basic' => 'Basic',
				'name' => 'Name',
				'address' => 'Address',
				'digital-contact' => 'Digital contact',
				'birthday' => 'Birthday',
				'credit-card' => 'Credit card',
				'password' => 'Password',
				'other' => 'Other',

			]
		],
	],
	'autocomplete_modifier' => [
		'label' => 'Autocomplete modifier',
		'config' => [
			'type' => 'select',
			'renderType' => 'selectSingle',
			'default' => '',
			'items' => TcaUtility::selectItemsHelper([
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
				'address-group' => 'Address group',
				'contact-type' => 'Contact type',
			]
		]
	],
	'display_condition' => [
		'label' => 'Server-side display condition',
		'description' => 'Condition expression in Symfony Expression Language. Useful for multi-page forms and field variants.',
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
		'label' => 'Client-side display condition',
		'description' => 'Condition expression in subscript. Useful for conditions based on field values on the same page.',
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