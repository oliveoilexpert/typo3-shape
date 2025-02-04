<?php

use UBOS\Shape\Utility\TcaUtility as Util;

$showItemBase = '
	--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, 
        sys_language_uid, 
        l10n_parent, 
        l10n_diffsource, 
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, 
        hidden';
$showItem = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
        --palette--;;general-default, 
	--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
        --palette--;;appearance,
    --div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
		--palette--;;advanced-default, 
    --div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
    	--palette--;;condition,' . $showItemBase;

return [
	'0' => [
		'showitem' => $showItem,
	],
	'text' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-text-input, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-text-input, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
	],
	'textarea' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-text-input, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-text-input, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
	],
	'email' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-text-input, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-text-input, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'autocomplete' => [
				'config' => [
					'default' => 'email',
				]
			]
		]
	],
	'tel' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-text-input, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-text-input, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'autocomplete' => [
				'config' => [
					'default' => 'tel',
				]
			]
		]
	],
	'password' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-password, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-text-input, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'autocomplete' => [
				'config' => [
					'default' => 'new-password',
				]
			]
		]
	],
	'search' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-text-input, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-text-input, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
	],
	'url' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-text-input, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-text-input, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'autocomplete' => [
				'config' => [
					'default' => 'url',
				]
			]
		]
	],
	'number' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-text-input, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-number-input, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'default_value' => [
				'config' => [
					'type' => 'input',
					'eval' => 'is_in',
					'is_in' => '0123456789.',
					'default' => null
				],
			],
			'min' => [
				'config' => [
					'type' => 'number',
					'format' => 'decimal',
					'mode' => 'useOrOverridePlaceholder',
					'nullable' => true,
					'default' => null
				],
			],
			'max' => [
				'config' => [
					'type' => 'number',
					'format' => 'decimal',
					'mode' => 'useOrOverridePlaceholder',
					'nullable' => true,
					'default' => null
				],
			],
		],
	],
	'range' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-default, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-number-input, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'default_value' => [
				'config' => [
					'type' => 'input',
					'eval' => 'is_in',
					'is_in' => '0123456789.',
					'default' => null
				],
			],
			'min' => [
				'config' => [
					'type' => 'number',
					'format' => 'decimal',
					'mode' => 'useOrOverridePlaceholder',
					'nullable' => true,
					'default' => null
				],
			],
			'max' => [
				'config' => [
					'type' => 'number',
					'format' => 'decimal',
					'mode' => 'useOrOverridePlaceholder',
					'nullable' => true,
					'default' => null
				],
			],
		],
	],
	'checkbox' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-default, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-default, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'default_value' => [
				'label' => Util::t('field.checked'),
				'config' => [
					'type' => 'check',
				]
			]
		]
	],
	'select' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-option-input, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-default, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
	],
	'radio' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-option-input, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-default, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
	],
	'multi-select' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-option-input, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-default, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
	],
	'multi-checkbox' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-option-input, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-default, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
	],
	'country-select' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-default, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-country-select, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'datalist' => [
				'label' => Util::t('field.allowed_countries'),
			],
		],
	],
	'date' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-default, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-datetime-input, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'default_value' => [
				'config' => [
					'type' => 'datetime',
					'format' => 'date',
				],
			],
			'min' => [
				'config' => [
					'type' => 'datetime',
					'format' => 'date',
				],
			],
			'max' => [
				'config' => [
					'type' => 'datetime',
					'format' => 'date',
				],
			],
		],
	],
	'datetime-local' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-default, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-datetime-input, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'default_value' => [
				'config' => [
					'type' => 'datetime',
					'format' => 'datetime',
				],
			],
			'min' => [
				'config' => [
					'type' => 'datetime',
					'format' => 'datetime',
				],
			],
			'max' => [
				'config' => [
					'type' => 'datetime',
					'format' => 'datetime',
				],
			],
		],
	],
	'time' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-default, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-datetime-input, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'default_value' => [
				'config' => [
					'type' => 'datetime',
					'format' => 'time',
				],
			],
			'min' => [
				'config' => [
					'type' => 'datetime',
					'format' => 'time',
				],
			],
			'max' => [
				'config' => [
					'type' => 'datetime',
					'format' => 'time',
				],
			],
		],
	],
	'month' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-default, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-datetime-input, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'default_value' => [
				'config' => [
					'type' => 'datetime',
					'format' => 'date',
				],
			],
			'min' => [
				'config' => [
					'type' => 'datetime',
					'format' => 'date',
				],
			],
			'max' => [
				'config' => [
					'type' => 'datetime',
					'format' => 'date',
				],
			],
		],
	],
	'week' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-default, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-datetime-input, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'default_value' => [
				'config' => [
					'type' => 'datetime',
					'format' => 'date',
				],
			],
			'min' => [
				'config' => [
					'type' => 'datetime',
					'format' => 'date',
				],
			],
			'max' => [
				'config' => [
					'type' => 'datetime',
					'format' => 'date',
				],
			],
		],
	],
	'file' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-file, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-file, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'min' => [
				'label' => Util::t('field.min_filesize'),
				'config' => [
					'type' => 'number',
					'format' => 'integer',
				]
			],
			'max' => [
				'label' => Util::t('field.max_filesize'),
				'config' => [
					'type' => 'number',
					'format' => 'integer',
				]
			]
		]
	],
	'reset' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			label, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-default, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
	],
	'hidden' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-default, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.advanced,
			--palette--;;advanced-default, 
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
	],
	'captcha' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			label, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
	],
	'content-header' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-content-header, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
	],
	'content-rte' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-content-rte, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'description' => [
				'config' => [
					'enableRichtext' => true,
				]
			],
		]
	],
	'content-element' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-content-element,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'default_value' => [
				'label' => Util::t('field.content_element'),
				'config' => [
					'type' => 'group',
					'allowed' => 'tt_content',
					'maxitems' => 1,
					'size' => 1,
				]
			],
		]
	],
	'repeatable-container' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-repeatable-container, 
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:appearance,
			--palette--;;appearance,
		--div--;LLL:EXT:shape/Resources/Private/Language/locallang_db.xlf:tab.condition,
			--palette--;;condition,' . $showItemBase,
	]
];