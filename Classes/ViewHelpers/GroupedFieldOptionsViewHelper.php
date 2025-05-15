<?php

namespace UBOS\Shape\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class GroupedFieldOptionsViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument('field', 'object', '', false, null);
	}

	public function render(): array
	{
		$field = $this->arguments['field'] ?: $this->renderChildren() ?: null;
		if (!$field instanceof \UBOS\Shape\Domain\Record\FieldRecord) {
			return [];
		}
		$groupedOptions = [];
		$groupLabel = '';
		foreach ($field->get('field_options') as $option) {
			if ($option->get('group_label')) {
				$groupLabel = $option->get('group_label');
			}
			if (!isset($groupedOptions[$groupLabel])) {
				$groupedOptions[$groupLabel] = [];
			}
			$groupedOptions[$groupLabel][] = $option;
		}
		return $groupedOptions;
	}

}
