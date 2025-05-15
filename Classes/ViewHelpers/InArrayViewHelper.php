<?php

namespace UBOS\Shape\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class InArrayViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		$this->registerArgument('needle', 'mixed', '', false, null);
		$this->registerArgument('haystack', 'mixed', '', false, null);
		$this->registerArgument('strict', 'bool', '', false, false);
	}

	public function render(): bool
	{
		$needle = $this->arguments['needle'] ?: $this->renderChildren() ?: null;
		$haystack = $this->arguments['haystack'] ?: null;
		if (!is_array($haystack)) {
			return $haystack === $needle;
		}
		return in_array($needle, $haystack, (bool)$this->arguments['strict']);
	}

}
