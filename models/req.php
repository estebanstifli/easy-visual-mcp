<?php
// Req mínimo para EasyVisualMcp (stub)
class EasyVisualMcpReq {
	public static function getRequestUri() {
		return isset($_SERVER['REQUEST_URI']) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	}
}
