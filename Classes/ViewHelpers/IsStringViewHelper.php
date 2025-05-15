<?php

namespace UBOS\Shape\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class IsStringViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument('value', 'mixed', '', false, null);
	}

	public function render(): bool
	{
		$var = $this->arguments['value'] ?: $this->renderChildren() ?: null;
		return is_string($var);
	}

}
