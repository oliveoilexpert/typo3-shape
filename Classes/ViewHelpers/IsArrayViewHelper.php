<?php

namespace UBOS\Shape\ViewHelpers;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class IsArrayViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		// name, type, description, required, default, escape
		$this->registerArgument('variable', 'mixed', '', false, null);
	}

	public function render(): bool
	{
		$var = $this->arguments['variable'] ?: $this->renderChildren() ?: null;
		return is_array($var);
	}

}
