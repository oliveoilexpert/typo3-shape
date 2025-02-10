<?php

return [
	'general-default' => [
		'showitem' => '
		page_parents,
		--linebreak--,
		label, name, 
		--linebreak--, 
		type, default_value, required, 
		--linebreak--, 
		description',
	],
	'general-text-input' => [
		'showitem' => '
		page_parents,
		--linebreak--,
		label, name, 
		--linebreak--, 
		type, default_value, required, 
		--linebreak--, 
		description, placeholder',
	],
	'general-option-input' => [
		'showitem' => '
		page_parents,
		--linebreak--,
		label, name, 
		--linebreak--, 
		type, required, 
		--linebreak--, 
		field_options,
		--linebreak--,
		description',
	],
	'general-password' => [
		'showitem' => '
		page_parents,
		--linebreak--,
		label, name, 
		--linebreak--, 
		type, required, 
		--linebreak--, 
		description, placeholder',
	],
	'general-file' => [
		'showitem' => '
		page_parents,
		--linebreak--,
		label, name, 
		--linebreak--, 
		type, required, 
		--linebreak--, 
		description',
	],
	'general-content-rte' => [
		'showitem' => '
		page_parents,
		--linebreak--,
		type,
		--linebreak--, 
		label, 
		--linebreak--, 
		description',
	],
	'general-content-header' => [
		'showitem' => '
		page_parents,
		--linebreak--,
		type,
		--linebreak--, 
		label',
	],
	'general-content-element' => [
		'showitem' => '
		page_parents,
		--linebreak--,
		type,
		--linebreak--, 
		label,
		--linebreak--,
		default_value',
	],
	'general-repeatable-container' => [
		'showitem' => '
		page_parents,
		--linebreak--,
		type, 
		--linebreak--, 
		label, name, 
		--linebreak--, 
		fields,
		--linebreak--,
		description',
	],
	'advanced-default' => [
		'showitem' => 'disabled, readonly',
	],
	'advanced-text-input' => [
		'showitem' => '
		disabled, readonly,
		--linebreak--,
		pattern, maxlength,
		--linebreak--,
		autocomplete_modifier, autocomplete,
		--linebreak--,
		datalist',
	],
	'advanced-datetime-input' => [
		'showitem' => '
		disabled, readonly,
		--linebreak--,
		min, max,
		--linebreak--,
		autocomplete_modifier, autocomplete,
		--linebreak--,
		datalist'
	],
	'advanced-number-input' => [
		'showitem' => '
		disabled, readonly,
		--linebreak--,
		min, max, step,
		--linebreak--,
		datalist'
	],
	'advanced-country-select' => [
		'showitem' => '
		disabled, readonly,
		--linebreak--,
		datalist',
	],
	'advanced-file' => [
		'showitem' => '
		disabled, readonly, multiple,
		--linebreak--,
		accept, 
		--linebreak--, 
		min, max',
	],
	'appearance' => [
		'showitem' => '
		layout, css_class, 
		--linebreak--, 
		width, validation_message, 
		--linebreak--, 
		rte_label',
	],
	'condition' => [
		'showitem' => 'display_condition, --linebreak--, js_display_condition',
	],
];