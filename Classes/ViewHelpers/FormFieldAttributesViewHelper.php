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
			'data-yf-control' => $record->get('name'),
		];
		if ($record->has('validation_message') && $record->get('validation_message')) {
			$attributes['data-yf-validation-message'] = $record->get('validation_message');
		}
		if ($record->has('datalist') && $record->get('datalist')) {
			$attributes['list'] = $this->templateVariableContainer->get('idPrefix') . 'co' . $record->getUid() . '-datalist';
		}
		foreach (['required', 'readonly', 'disabled', 'multiple'] as $attribute) {
			if (!$record->has($attribute)) continue;
			$val = $record->get($attribute);
			if ($val) {
				$attributes[$attribute] = '';
			}
		}
		foreach (['step', 'pattern', 'maxlength', 'placeholder'] as $attribute) {
			if (!$record->has($attribute)) continue;
			$val = $record->get($attribute);
			if ($val) {
				$attributes[$attribute] = (string)$val;
			}
		}
		foreach (['min', 'max'] as $attribute) {
			if (!$record->has($attribute)) continue;
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
