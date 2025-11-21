<?php
// Req mínimo para StifliFlexMcp (stub)
class StifliFlexMcpReq {
	public static function getRequestUri() {
		return isset($_SERVER['REQUEST_URI']) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	}
}
