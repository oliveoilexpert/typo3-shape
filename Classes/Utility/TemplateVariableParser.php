<?php
declare(strict_types=1);

namespace UBOS\Shape\Utility;

/**
 * Parser for interpolating variables in templates.
 * Supports simple variable replacement and array to list conversion.
 *
 * Syntax:
 * - Simple variable: {{ variable }}
 * - Array to list: {{ array[] }}
 * - Array property to list: {{ array[].property }}
 * - Nested array to list: {{ array.nested[] }}
 */
class TemplateVariableParser
{
	/**
	 * Parses a template string and replaces variables with their corresponding values.
	 *
	 * @param string $template The template string containing variables to replace
	 * @param array<string, mixed> $data Array containing the variable values
	 * @param bool $escapeHtml Whether to escape HTML special characters in the values
	 * @return string The processed template with all variables replaced
	 */
	public static function parse(string $template, array $data, bool $escapeHtml = false): string
	{
		return preg_replace_callback(
			'/\{\{\s*([^}]+?)\s*\}\}/',
			fn($matches) => self::parsePlaceholder(trim($matches[1]), $data, $escapeHtml),
			$template
		);
	}

	/**
	 * Parses a single placeholder and returns its replacement value.
	 */
	private static function parsePlaceholder(string $path, array $data, bool $escapeHtml): string
	{
		// Check if this is an array operation
		if (str_contains($path, '[]')) {
			return self::handleArrayOperation($path, $data, $escapeHtml);
		}

		// Handle simple variable
		$value = self::getValue($data, explode('.', $path));
		if ($value === null || is_array($value)) {
			return '{{' . $path . '}}';
		}

		return $escapeHtml ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : $value;
	}

	/**
	 * Handles array to list operations.
	 */
	private static function handleArrayOperation(string $path, array $data, bool $escapeHtml): string
	{
		// Split path at the [] operator
		$parts = explode('[]', $path);

		// Get the array
		$arrayPath = trim($parts[0]);
		$array = self::getValue($data, explode('.', $arrayPath));

		if (!is_array($array)) {
			return '{{' . $path . '}}';
		}

		// If there's a property path after [], map that property
		if (isset($parts[1]) && $parts[1] !== '') {
			$propertyPath = ltrim($parts[1], '.');
			$values = [];
			foreach ($array as $item) {
				if (!is_array($item)) {
					continue;
				}
				$value = self::getValue($item, explode('.', $propertyPath));
				if ($value !== null && !is_array($value)) {
					$values[] = $value;
				}
			}
		} else {
			// Simple array to list
			$values = array_filter(
				$array,
				fn($v) => $v !== null && !is_array($v)
			);
		}

		return self::formatValues($values, $escapeHtml);
	}

	/**
	 * Gets a value from a nested array structure using a path array.
	 */
	private static function getValue(array $data, array $path): string|array|null
	{
		$value = $data;
		foreach ($path as $key) {
			if (!isset($value[$key])) {
				return null;
			}
			$value = $value[$key];
		}

		return $value;
	}

	/**
	 * Formats an array of values into a comma-separated string.
	 */
	private static function formatValues(array $values, bool $escapeHtml): string
	{
		if (empty($values)) {
			return '';
		}

		return $escapeHtml
			? implode(', ', array_map(fn($v) => htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'), $values))
			: implode(', ', array_map('strval', $values));
	}
}