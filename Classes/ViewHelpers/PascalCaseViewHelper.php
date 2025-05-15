<?php

namespace UBOS\Shape\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class PascalCaseViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument('string', 'string', '', false, null);
	}

	public function render(): string
	{
		$string = $this->arguments['string'] ?: $this->renderChildren() ?: '';
		return preg_replace_callback('/^[a-z]|[_\-\s]+([a-zA-Z])/m', fn($m) => isset($m[1]) ? strtoupper($m[1]) : strtoupper($m[0]), ucfirst($string));
	}

}
