<?php

namespace UBOS\Shape\Domain\Finisher;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use UBOS\Shape\Domain;
use UBOS\Shape\Utility\TemplateVariableParser;

class SaveToDatabaseFinisher extends AbstractFinisher
{
	protected array $settings = [
		'table' => '',
		'storagePage' => '',
		'whereColumn' => '',
		'whereValue' => '',
		'columns' => [],
	];

	public function execute(): void
	{
		if (!$this->settings['table']) {
			return;
		}

		$genericRepository = new Domain\Repository\GenericRepository($this->settings['table']);

		$values = [
			'pid' => (int)($this->settings['storagePage'] ?: $this->getPlugin()->getPid() ?? $this->getForm()->getPid()),
			'tstamp' => time(),
		];

		foreach ($this->settings['columns'] as $item) {
			$column = $item['column'] ?? null;
			if (!$column) continue;
			$name = $column['name'];
			$value = $this->parseWithValues($column['value']);
			$values[$name] = $value;
		}

		if ($this->settings['whereColumn'] && $this->settings['whereValue']) {
			$whereColumn = $this->settings['whereColumn'];
			$whereValue = $this->parseWithValues($this->settings['whereValue']);
			$genericRepository->updateBy($whereColumn, $whereValue, $values);
		} else {
			$values['crdate'] = time();
			$genericRepository->create($values);
		}
	}

	protected function parseWithValues(string $string): string
	{
		return TemplateVariableParser::parse($string, $this->getFormValues());
	}
}