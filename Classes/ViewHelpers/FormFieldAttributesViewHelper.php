<?php

namespace UBOS\Shape\ViewHelpers;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class FormFieldAttributesViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		// name, type, description, required, default, escape
		$this->registerArgument('record', 'object', '', true);
		$this->registerArgument('attributes', 'array', '', false, []);
	}

	public function render(): array
	{
		$record = $this->arguments['record'];
		$attributes = [
			'data-shape-field' => $record->get('identifier'),
		];
		if ($record->get('validation_message')) {
			$attributes['data-shape-validity-message'] = $record->get('validation_message');
		}
		if ($record->get('list')) {
			$attributes['list'] = $this->templateVariableContainer->get('fieldIdPrefix') . $record->get('identifier') . '--list';
		}
		foreach (['required', 'readonly', 'disabled', 'multiple'] as $attribute) {
			$val = $record->get($attribute);
			if ($val) {
				$attributes[$attribute] = '';
			}
		}
		foreach (['step', 'pattern', 'maxlength', 'placeholder'] as $attribute) {
			$val = $record->get($attribute);
			if ($val) {
				$attributes[$attribute] = (string)$val;
			}
		}
		foreach (['min', 'max'] as $attribute) {
			$val = $record->get($attribute);
			if ($val || $val === 0) {
				$attributes[$attribute] = (string)$val;
			}
		}
		if ($record->has('autocomplete') && $record->get('autocomplete')) {
			$attributes['autocomplete'] = $record->get('autocomplete_modifier') . $record->get('autocomplete');
		}
		return array_merge($attributes, $this->arguments['attributes']);
	}

}
