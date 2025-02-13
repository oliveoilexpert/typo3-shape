<?php

namespace UBOS\Shape\ViewHelpers;

use TYPO3\CMS\Core\Utility\DebugUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class BasenameViewHelper extends AbstractViewHelper
{
	public function initializeArguments(): void
	{
		// name, type, description, required, default, escape
		$this->registerArgument('path', 'string', '', false, '');
	}

	public function render(): string
	{
		$path = $this->arguments['path'] ?: $this->renderChildren() ?: '';
		return basename($path);
	}

}
