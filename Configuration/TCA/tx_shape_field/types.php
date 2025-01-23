<?php

$showItem = '
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
        --palette--;;base, 
        --palette--;;detail,
	--div--;Appearance,
        --palette--;;appearance,
    --div--;Attributes,
		--palette--;;attributes, 
    	--palette--;;autocomplete,   	
    --div--;Condition,
    	--palette--;;condition, 
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, 
        sys_language_uid, 
        l10n_parent, 
        l10n_diffsource, 
    --div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access, 
        hidden';

return [
	'0' => [
		'showitem' => $showItem,
	],
	'number' => [
		'showitem' => $showItem,
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
		'showitem' => $showItem,
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
	'date' => [
		'showitem' => $showItem,
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
		'showitem' => $showItem,
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
		'showitem' => $showItem,
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
		'showitem' => $showItem,
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
		'showitem' => $showItem,
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
	'email' => [
		'showitem' => $showItem,
		'columnsOverrides' => [
			'autocomplete' => [
				'config' => [
					'default' => 'email',
				]
			]
		]
	],
	'tel' => [
		'showitem' => $showItem,
		'columnsOverrides' => [
			'autocomplete' => [
				'config' => [
					'default' => 'tel',
				]
			]
		]
	],
	'password' => [
		'showitem' => $showItem,
		'columnsOverrides' => [
			'autocomplete' => [
				'config' => [
					'default' => 'new-password',
				]
			]
		]
	],
	'url' => [
		'showitem' => $showItem,
		'columnsOverrides' => [
			'autocomplete' => [
				'config' => [
					'default' => 'url',
				]
			]
		]
	],
	'checkbox' => [
		'showitem' => $showItem,
		'columnsOverrides' => [
			'default_value' => [
				'label' => 'Checked',
				'config' => [
					'type' => 'check',
				]
			]
		]
	],
	'file' => [
		'showitem' => $showItem,
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
	'header' => [
		'showitem' => '
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
					--palette--;;rte, 
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, 
					sys_language_uid, 
					l10n_parent, 
					l10n_diffsource, 
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
					hidden',
		'columnsOverrides' => [
		]
	],
	'rte' => [
		'showitem' => '
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
					--palette--;;rte, 
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, 
					sys_language_uid, 
					l10n_parent, 
					l10n_diffsource, 
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
					hidden',
		'columnsOverrides' => [
			'description' => [
				'config' => [
					'enableRichtext' => true,
				]
			],
		]
	],
	'repeatable-container' => [
		'showitem' => '
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:general, 
					--palette--;;repeatable-container, 
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:language, 
					sys_language_uid, 
					l10n_parent, 
					l10n_diffsource, 
				--div--;LLL:EXT:core/Resources/Private/Language/Form/locallang_tabs.xlf:access,
					hidden',
	]
];