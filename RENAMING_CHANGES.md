# Renombrado de Plugin: Easy Visual MCP → StifLi Flex MCP

## Resumen de Cambios

Este documento registra todos los cambios realizados para renombrar el plugin de "Easy Visual MCP" a "StifLi Flex MCP" para cumplir con las políticas de WordPress.org.

## ✅ Cambios Completados

### 1. Metadatos del Plugin
- **Archivo principal renombrado**: `easy-visual-mcp.php` → `stifli-flex-mcp.php`
- **Plugin Name**: Easy Visual MCP → StifLi Flex MCP
- **Plugin URI**: `https://github.com/estebanstifli/easy-visual-mcp` → `https://github.com/estebanstifli/stifli-flex-mcp`
- **Text Domain**: `easy-visual-mcp` → `stifli-flex-mcp`

### 2. Nombres de Clases PHP
Todos los nombres de clases actualizados en todo el código:
- `EasyVisualMcp` → `StifliFlexMcp`
- `EasyVisualMcpUtils` → `StifliFlexMcpUtils`
- `EasyVisualMcpModel` → `StifliFlexMcpModel`
- `EasyVisualMcpFrame` → `StifliFlexMcpFrame`
- `EasyVisualMcpDispatcher` → `StifliFlexMcpDispatcher`
- `EasyVisualMcpReq` → `StifliFlexMcpReq`
- `EasyVisualMcp_WC_Products` → `StifliFlexMcp_WC_Products`
- `EasyVisualMcp_WC_Orders` → `StifliFlexMcp_WC_Orders`
- `EasyVisualMcp_WC_Customers` → `StifliFlexMcp_WC_Customers`
- `EasyVisualMcp_WC_Coupons` → `StifliFlexMcp_WC_Coupons`
- `EasyVisualMcp_WC_System` → `StifliFlexMcp_WC_System`

### 3. Prefijos de Funciones
- `easy_visual_mcp_*` → `stifli_flex_mcp_*`
  - Ejemplo: `easy_visual_mcp_log()` → `stifli_flex_mcp_log()`
  - Ejemplo: `easy_visual_mcp_activate()` → `stifli_flex_mcp_activate()`

### 4. Constantes
- `EVMCP_DEBUG` → `SFLMCP_DEBUG`
- `[EVMCP]` (log prefix) → `[SFLMCP]`

### 5. Prefijos de Tablas de Base de Datos
Todas las tablas renombradas:
- `wp_evmcp_queue` → `wp_sflmcp_queue`
- `wp_evmcp_tools` → `wp_sflmcp_tools`
- `wp_evmcp_profiles` → `wp_sflmcp_profiles`
- `wp_evmcp_profile_tools` → `wp_sflmcp_profile_tools`

### 6. Opciones de WordPress
- `easy_visual_mcp_token` → `stifli_flex_mcp_token`
- `easy_visual_mcp_token_user` → `stifli_flex_mcp_token_user`

### 7. Hooks y Acciones AJAX
- `evmcp_clean_queue` → `sflmcp_clean_queue`
- `evmcp_generate_token` → `sflmcp_generate_token`
- `evmcp_create_profile` → `sflmcp_create_profile`
- `evmcp_update_profile` → `sflmcp_update_profile`
- `evmcp_delete_profile` → `sflmcp_delete_profile`
- `evmcp_duplicate_profile` → `sflmcp_duplicate_profile`
- `evmcp_apply_profile` → `sflmcp_apply_profile`
- `evmcp_export_profile` → `sflmcp_export_profile`
- `evmcp_import_profile` → `sflmcp_import_profile`
- `evmcp_restore_system_profiles` → `sflmcp_restore_system_profiles`
- `allow_evmcp` → `allow_sflmcp`
- `evmcp_callback` → `sflmcp_callback`
- `evmcp-admin` (nonce) → `sflmcp-admin`
- `evmcp_profiles` (nonce) → `sflmcp_profiles`

### 8. REST API Namespace
- `/wp-json/easy-visual-mcp/v1/` → `/wp-json/stifli-flex-mcp/v1/`
  - Endpoint SSE: `/wp-json/stifli-flex-mcp/v1/sse`
  - Endpoint Messages: `/wp-json/stifli-flex-mcp/v1/messages`

### 9. Slug del Menú de Administración
- `easy-visual-mcp` → `stifli-flex-mcp`
- URLs del admin: `?page=stifli-flex-mcp&tab=...`

### 10. Documentación
Archivos actualizados:
- ✅ `readme.txt` - Todas las referencias al nombre y slug
- ✅ `dev/*.md` - Todos los archivos markdown
- ✅ `.github/copilot-instructions.md`
- ✅ `languages/README.md`
- ✅ `checktest.md`
- ✅ `examples/*` - Archivos de ejemplo

### 11. Cadenas de Traducción
Todas las cadenas en `mod.php` actualizadas para usar el nuevo text domain `stifli-flex-mcp`.

## 📋 Notas de Migración

### Para Usuarios Existentes
Los usuarios que actualicen desde "Easy Visual MCP" necesitarán:
1. **Reactivar el plugin** después de la actualización
2. **Regenerar tokens** (las opciones antiguas permanecen en la BD pero con nombres diferentes)
3. **Actualizar endpoints** en integraciones externas:
   - Antiguo: `/wp-json/easy-visual-mcp/v1/messages`
   - Nuevo: `/wp-json/stifli-flex-mcp/v1/messages`

### Tablas de Base de Datos
Las tablas antiguas (`wp_evmcp_*`) NO se migran automáticamente. Los usuarios deberán:
- Opción 1: Desinstalar la versión antigua (borra tablas `wp_evmcp_*`)
- Opción 2: Migración manual de datos si es necesario

### Script de Migración (Opcional)
Si se requiere preservar configuraciones existentes, se puede crear un script de migración que:
1. Copie `easy_visual_mcp_token` → `stifli_flex_mcp_token`
2. Copie `easy_visual_mcp_token_user` → `stifli_flex_mcp_token_user`
3. Migre tablas `wp_evmcp_*` → `wp_sflmcp_*`

## 🔍 Verificación

### Comandos de Búsqueda
Para verificar que no quedan referencias antiguas:

```powershell
# Buscar referencias en archivos PHP
Get-ChildItem -Path . -Filter "*.php" -Recurse | Select-String -Pattern "easy.visual.mcp|EasyVisualMcp|easy_visual_mcp|EVMCP|evmcp" -CaseSensitive

# Buscar en archivos de documentación
Get-ChildItem -Path . -Filter "*.md" -Recurse | Select-String -Pattern "easy.visual.mcp|EasyVisualMcp"
```

### Archivos Principales a Revisar
- ✅ `stifli-flex-mcp.php` - Archivo principal renombrado
- ✅ `mod.php` - Clase principal y rutas REST
- ✅ `models/model.php` - Lógica de herramientas
- ✅ `models/utils.php` - Utilidades
- ✅ `uninstall.php` - Script de desinstalación
- ✅ `readme.txt` - Documentación oficial

## 📦 Próximos Pasos

1. **Actualizar repositorio GitHub**:
   - Renombrar repositorio de `easy-visual-mcp` a `stifli-flex-mcp`
   - Actualizar README.md del repositorio
   - Crear nuevo tag v1.0.0 con el nombre actualizado

2. **Empaquetar para distribución**:
   ```powershell
   .\dev\build-plugin.ps1 -VersionTag "1.0.0"
   ```

3. **Enviar a WordPress.org**:
   - Usar el nuevo slug `stifli-flex-mcp`
   - Actualizar la documentación de assets

## ✅ Checklist Final

- [x] Plugin Name actualizado
- [x] Plugin URI actualizado
- [x] Text Domain actualizado
- [x] Todas las clases renombradas
- [x] Todas las funciones renombradas
- [x] Todas las constantes renombradas
- [x] Prefijos de tablas actualizados
- [x] Opciones de WordPress renombradas
- [x] Hooks y acciones AJAX actualizados
- [x] REST API namespace actualizado
- [x] Slug del menú admin actualizado
- [x] Documentación actualizada
- [x] Archivos de ejemplo actualizados
- [x] Archivo principal renombrado
- [x] Cadenas de traducción actualizadas

---

**Fecha de cambio**: 21 de noviembre de 2025
**Versión**: 1.0.0
**Razón**: Cumplimiento con políticas de WordPress.org
