<?php
// Utilidades mínimas para EasyVisualMcp (stub)
class EasyVisualMcpUtils {
	public static function getUserAgent() {
		return $_SERVER['HTTP_USER_AGENT'] ?? '';
	}
	public static function getIP() {
		return $_SERVER['REMOTE_ADDR'] ?? '';
	}
	public static function setAdminUser() {
		// Implementar si es necesario
	}
	public static function getArrayValue($arr, $key, $default = null, $depth = 1) {
		if (!is_array($arr)) return $default;
		if (!array_key_exists($key, $arr)) return $default;
		return $arr[$key];
	}
	public static function estimateToolTokenUsage(array $toolDef): int {
		$name = isset($toolDef['name']) ? (string) $toolDef['name'] : '';
		$description = isset($toolDef['description']) ? (string) $toolDef['description'] : '';
		$inputSchema = isset($toolDef['inputSchema']) ? $toolDef['inputSchema'] : null;
		$additional = array();
		foreach (array('confirmPrompt', 'outputSchema', 'examples') as $extraKey) {
			if (isset($toolDef[$extraKey])) {
				$additional[] = wp_json_encode($toolDef[$extraKey], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			}
		}
		$parts = array($name, $description);
		if (null !== $inputSchema) {
			$parts[] = wp_json_encode($inputSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		}
		if (!empty($additional)) {
			$parts = array_merge($parts, $additional);
		}
		$payload = trim(implode("\n", array_filter($parts, 'strlen')));
		if ('' === $payload) {
			return 0;
		}
		$charCount = strlen($payload);
		if ($charCount <= 0) {
			return 0;
		}
		return (int) ceil($charCount / 4);
	}
}
