# Copilot Instructions for Easy Visual MCP

## Project Overview
**Easy Visual MCP** is a WordPress plugin exposing WordPress management via JSON-RPC 2.0, designed for LLM integration (ChatGPT, Claude, etc.). It provides tool discovery (`tools/list`), execution (`tools/call`), and SSE streaming at `/wp-json/easy-visual-mcp/v1/`.

## Architecture & Data Flow

### Request Flow (JSON-RPC 2.0)
1. Client → `/wp-json/easy-visual-mcp/v1/messages` (POST) or `/sse` (GET for streaming)
2. `mod.php::canAccessMCP()` → token validation (Bearer header or `?token=` query param)
3. `mod.php::handleDirectJsonRPC()` → method routing (`initialize`, `tools/list`, `tools/call`)
4. `models/model.php::dispatchTool()` → tool execution with capability check
5. Response → JSON-RPC result or error

### Key Components
- **`easy-visual-mcp.php`**: Bootstrap (loads helpers/models, initializes `EasyVisualMcp`), table creation (`wp_evmcp_queue`, `wp_evmcp_tools`), seeding, cron scheduling
- **`mod.php`**: Core logic – REST API registration, auth (`canAccessMCP`), JSON-RPC dispatch, SSE streaming, **two-tab admin UI** (Settings + Tools Management)
- **`models/model.php`**: Tool registry (`getTools()`), dispatch logic (`dispatchTool()`), capability mapping (`getToolCapability()`), **tools filtering** (`getToolsList()`)
- **Helpers**: `utils.php` (safe array access), `dispatcher.php` (filter wrapper), `frame.php` (stub for logging)

### SSE Streaming (Critical for ChatGPT Connectors)
- SSE endpoint: `/wp-json/easy-visual-mcp/v1/sse` (GET or POST)
- On connect, sends `event: endpoint` with `/messages` URL for client to POST to
- Polls session queue every 200ms, sends `event: message` with JSON-RPC responses
- Sends `event: heartbeat` every 10s, `event: bye` on disconnect/timeout (5min idle)
- **Important**: Disables output buffering (`ob_end_flush()`, `X-Accel-Buffering: no`) to prevent CDN/proxy blocking
- Responses are buffered in MySQL table `wp_evmcp_queue`; messages expire after 5 min and the `evmcp_clean_queue` cron job (hourly) purges old rows.

## Tool Development Pattern

### Adding a New Tool (3-Step Pattern)
```php
// 1. Define in getTools() (models/model.php ~line 70)
'my_new_tool' => array(
    'name' => 'my_new_tool',
    'description' => 'Does X with Y. Returns Z.',
    'inputSchema' => array(
        'type' => 'object',
        'properties' => array(
            'param1' => array('type' => 'string'),
        ),
        'required' => array('param1'),
    ),
),

// 2. Add capability if mutating (models/model.php::getToolCapability ~line 693)
'my_new_tool' => 'edit_posts', // or null for public

// 3. Implement in dispatchTool() (models/model.php ~line 730)
case 'my_new_tool':
    $param1 = $args['param1'] ?? '';
    // ... logic using WP functions ...
    $addResultText($r, "Success message");
    return $r;

// 4. Add to wp_evmcp_tools table on activation (easy-visual-mcp.php::easy_visual_mcp_seed_initial_tools)
array('my_new_tool', 'Does X with Y. Returns Z.', 'WordPress - YourCategory', 1),
```

**Important**: Only enabled tools (where `enabled = 1` in `wp_evmcp_tools`) are returned by `getToolsList()`. Users can enable/disable tools from the admin UI Tools Management tab.

### Intent Classification (models/model.php::getIntentForTool)
- **`read`**: Public tools (no confirmation) – e.g., `wp_get_posts`, `wp_get_users`
- **`sensitive_read`**: Requires confirmation – `wp_get_option`, `wp_get_post_meta`, `fetch`
- **`write`**: Requires confirmation – all `wp_create_*`, `wp_update_*`, `wp_delete_*`, plugin/theme install

## Authentication & Security Patterns

### Token Validation (mod.php::canAccessMCP)
```php
// Priority order:
// 1. If no token configured → allow public access
// 2. Check Authorization: Bearer <token>
// 3. Fallback to ?token=<token> query param
// 4. On match → wp_set_current_user() to mapped user or admin
// 5. Apply filters 'allow_evmcp' (extensible)
```

### Capability Enforcement (models/model.php::dispatchTool)
```php
$required_cap = $this->getToolCapability($tool);
if ($required_cap && !current_user_can($required_cap)) {
    $r['error'] = array('code' => -32603, 'message' => 'Insufficient permissions');
    return $r;
}
```

## Critical Developer Workflows

### Testing with REST Client
Use `examples/wordpress-mcp.http`:
```http
POST https://your-site.test/wp-json/easy-visual-mcp/v1/messages
Authorization: Bearer YOUR_TOKEN
Content-Type: application/json

{
  "jsonrpc": "2.0",
  "method": "tools/call",
  "params": {"name": "mcp_ping", "arguments": {}},
  "id": 1
}
```

### Debugging (WP_DEBUG mode)
- Enable: `define('WP_DEBUG', true);` in `wp-config.php`
- Logs in `mod.php`: auth flow (`canAccessMCP`), SSE events, token masking
- Logs in `models/model.php`: tool not found, capability checks
- Check WordPress debug.log or error_log

### Admin UI (Settings & Tools Management)
- **Location**: WordPress Admin → Easy Visual MCP (top-level menu with dashicons-rest-api icon)
- **Two Tabs**:
  - **Settings**: Generate/revoke tokens (AJAX `evmcp_generate_token`, `evmcp_revoke_token`), map token to WP user, endpoint URLs
  - **Tools Management**: Enable/disable tools per category, updates `wp_evmcp_tools` table
- **Registered in**: `mod.php::registerAdmin()`, `mod.php::renderSettingsTab()`, `mod.php::renderToolsTab()`

## Project-Specific Conventions

### Safe Array Access Pattern
Always use `EasyVisualMcpUtils::getArrayValue($arr, 'key', $default)` instead of direct array access to prevent notices.

### Result Construction (dispatchTool)
```php
// Use helper closure to add text results:
$addResultText($r, "Human-readable output");
// Or set structured result:
$r['result'] = array('content' => [array('type' => 'text', 'text' => '...')]);
```

### Error Handling (JSON-RPC codes)
- `-32700`: Parse error (invalid JSON)
- `-32600`: Invalid Request (method missing)
- `-32601`: Method not found
- `-32603`: Internal error / Insufficient permissions
- `-44001`: Custom "Method not found" fallback
- `-44000`: Internal exception

### HTML Sanitization
Use `$cleanHtml = function($v) { return wp_kses_post( wp_unslash( $v ) ); };` for user-provided HTML content.

### JSON-RPC ID Handling
The `id` field in JSON-RPC 2.0 can be **string, int, or null**. All methods handling `$id` (e.g., `handleCallback`, `dispatchTool`, `rpcError`) accept mixed types without type hints to comply with the spec.

## Migration & Extension

### Porting Tools from ai-copilot (see MIGRACION_TOOLS.md)
- Replace `WaicUtils` → `EasyVisualMcpUtils`
- Replace `WaicFrame` → `EasyVisualMcpFrame`
- Update tool array in `getTools()`
- Copy/adapt dispatch case blocks
- Test with `body_*.json` example files

### Current Status (TODO.md priorities)
- ✅ Basic tools (posts, users, comments, taxonomies, media, plugins)
- 🚧 OpenAI/ChatGPT `functions` adapter (in-progress)
- ⏳ Token validation for mutating tools (partially done)
- ⏳ Strict parameter validation against schemas

## External Dependencies & Integration

### WordPress APIs Used
- `wp_insert_post()`, `wp_update_post()`, `wp_delete_post()`
- `get_posts()`, `get_comments()`, `get_users()`
- `wp_insert_term()`, `wp_delete_term()`, `get_terms()`
- `wp_upload_bits()` for media (from `aiwu_image` tool)
- `activate_plugin()`, `deactivate_plugins()`, `plugins_api()`
- Plugin/Theme Upgrader API for installs

### LLM Integration Notes
- **ChatGPT Connectors**: MUST use SSE endpoint (not just `/messages`)
- **Tool Discovery**: Call `tools/list` first, then `tools/call` with `name` + `arguments`
- **Protocol Version**: Advertises `2025-06-18` in `initialize` response
- **Capabilities**: `tools.listChanged = true`, `prompts/resources = false`

## Quick Reference

### File Locations for Common Tasks
- Add tool: `models/model.php` → `getTools()` + `dispatchTool()`
- Add capability: `models/model.php` → `getToolCapability()`
- Modify auth: `mod.php` → `canAccessMCP()`
- Admin UI: `mod.php` → `registerAdmin()`, `settingsPage()`
- Test requests: `examples/wordpress-mcp.http` or `body_*.json` files
- Queue storage: `mod.php::storeMessage()` / `fetchMessages()` (table `wp_evmcp_queue`)
- Queue cleanup cron: `easy-visual-mcp.php::easy_visual_mcp_clean_queue()` (hook `evmcp_clean_queue` hourly)
- Tools database: `easy-visual-mcp.php::easy_visual_mcp_maybe_create_tools_table()`, `easy_visual_mcp_seed_initial_tools()` (table `wp_evmcp_tools`)

### No Build Step
Pure PHP plugin – edit files, reload WordPress. No npm/composer/webpack required.
- Test requests: `examples/wordpress-mcp.http` or `body_*.json` files
- Queue storage: `mod.php::storeMessage()` / `fetchMessages()` (table `wp_evmcp_queue`)
- Queue cleanup cron: `easy-visual-mcp.php::easy_visual_mcp_clean_queue()` (hook `evmcp_clean_queue` hourly)

### No Build Step
Pure PHP plugin – edit files, reload WordPress. No npm/composer/webpack required.