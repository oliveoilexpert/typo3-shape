<?php

namespace UBOS\Shape\Utility;

class TcaUtility
{
	public static function selectItemHelper(array $item): array
	{
		return [
			'label' => $item[0] ?? $item['label'],
			'value' => $item[1] ?? $item['value'],
			'icon' => $item[2] ?? $item['icon'] ??  '',
			'group' => $item[3] ?? $item['group'] ?? '',
		];
	}

	public static function selectItemsHelper(array $items): array
	{
		return array_map(function($item) {
			return self::selectItemHelper($item);
		}, $items);
	}

	public static function addFieldType(): void
	{

	}
}