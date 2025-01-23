<?php

return [
	'base' => [
		'showitem' => '
		label, identifier, 
		--linebreak--, 
		type, default_value, required, 
		--linebreak--,
		field_options,
		--linebreak--, 
		description, placeholder',
	],
	'config' => [
		'showitem' => '',
	],
	'detail' => [
		'showitem' => '',
	],
	'appearance' => [
		'label' => 'Appearance',
		'showitem' => 'layout, css_class, --linebreak--, width, validation_message, --linebreak--, rte_label',
	],
	'attributes' => [
		'label' => 'Attributes',
		'showitem' => '
		disabled, readonly, multiple, 
		--linebreak--, 
		pattern, accept, maxlength, 
		--linebreak--, 
		min, max, step',
	],
	'autocomplete' => [
		'showitem' => 'autocomplete_modifier, autocomplete',
	],
	'rte' => [
		'showitem' => 'type, --linebreak--, label, --linebreak--, description',
	],
	'repeatable-container' => [
		'showitem' => 'type, --linebreak--, label, identifier, --linebreak--, fields',
	],
	'condition' => [
		'label' => 'Dispaly condition',
		'showitem' => 'display_condition, --linebreak--, js_display_condition',
	],
	'validation' => [
		'showitem' => 'server_validators, --linebreak--, server_validators_options',
	]
];