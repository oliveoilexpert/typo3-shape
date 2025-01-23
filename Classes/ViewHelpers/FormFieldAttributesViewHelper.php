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
			'data-field-id' => $record->get('identifier'),
			// replace with event listener in script
			'onchange' => 'window.__tx_formal.evalConditions()'
		];
		if ($record->get('validation_message')) {
			// replace with data-custom-validation and event listener in script
			$attributes['oninvalid'] = 'this.setCustomValidity("' . $record->get('validation_message') . '")';
			$attributes['oninput'] = 'this.setCustomValidity("")';
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
