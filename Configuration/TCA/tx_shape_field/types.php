<?php

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
	--div--;Appearance,
        --palette--;;appearance,
    --div--;Advanced,
		--palette--;;advanced-default, 
    --div--;Condition,
    	--palette--;;condition,' . $showItemBase;

return [
	'0' => [
		'showitem' => $showItem,
	],
	'text' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-text-input, 
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-text-input, 
		--div--;Condition,
			--palette--;;condition,' . $showItemBase,
	],
	'textarea' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-text-input, 
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-text-input, 
		--div--;Condition,
			--palette--;;condition,' . $showItemBase,
	],
	'email' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-text-input, 
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-text-input, 
		--div--;Condition,
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
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-text-input, 
		--div--;Condition,
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
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-text-input, 
		--div--;Condition,
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
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-text-input, 
		--div--;Condition,
			--palette--;;condition,' . $showItemBase,
	],
	'url' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-text-input, 
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-text-input, 
		--div--;Condition,
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
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-number-input, 
		--div--;Condition,
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
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-number-input, 
		--div--;Condition,
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
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-default, 
		--div--;Condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'default_value' => [
				'label' => 'Checked',
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
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-default, 
		--div--;Condition,
			--palette--;;condition,' . $showItemBase,
	],
	'radio' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-option-input, 
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-default, 
		--div--;Condition,
			--palette--;;condition,' . $showItemBase,
	],
	'multi-select' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-option-input, 
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-default, 
		--div--;Condition,
			--palette--;;condition,' . $showItemBase,
	],
	'multi-checkbox' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-option-input, 
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-default, 
		--div--;Condition,
			--palette--;;condition,' . $showItemBase,
	],
	'country-select' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-default, 
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-country-select, 
		--div--;Condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'datalist' => [
				'label' => 'Allowed countries list (ISO 3166-1 alpha-2)',
			],
		],
	],
	'date' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-default, 
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-datetime-input, 
		--div--;Condition,
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
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-datetime-input, 
		--div--;Condition,
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
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-datetime-input, 
		--div--;Condition,
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
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-datetime-input, 
		--div--;Condition,
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
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-datetime-input, 
		--div--;Condition,
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
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-file, 
		--div--;Condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'min' => [
				'label' => 'Minimum file size in kB',
				'config' => [
					'type' => 'number',
					'format' => 'integer',
				]
			],
			'max' => [
				'label' => 'Maximum file size in kB',
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
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Advanced,
			--palette--;;advanced-default, 
		--div--;Condition,
			--palette--;;condition,' . $showItemBase,
	],
	'hidden' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-default, 
		--div--;Advanced,
			--palette--;;advanced-default, 
		--div--;Condition,
			--palette--;;condition,' . $showItemBase,
	],
	'captcha' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			label, 
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Condition,
			--palette--;;condition,' . $showItemBase,
	],
	'content-header' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-content-header, 
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Condition,
			--palette--;;condition,' . $showItemBase,
	],
	'content-rte' => [
		'showitem' => '
		--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
			--palette--;;general-content-rte, 
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Condition,
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
		--div--;Condition,
			--palette--;;condition,' . $showItemBase,
		'columnsOverrides' => [
			'default_value' => [
				'label' => 'Content element',
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
		--div--;Appearance,
			--palette--;;appearance,
		--div--;Condition,
			--palette--;;condition,' . $showItemBase,
	]
];