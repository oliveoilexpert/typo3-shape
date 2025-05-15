<?php

namespace UBOS\Shape\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class LineExplodeViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument('string', 'string', '', false, null);
	}

	public function render(): array
	{
		$string = $this->arguments['string'] ?: $this->renderChildren() ?: '';
		return array_map('trim', explode(PHP_EOL, $string));
	}

}
