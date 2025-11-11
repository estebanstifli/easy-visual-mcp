# Translation Files

This directory contains translation files for Easy Visual MCP.

## Generating the .pot file

To generate the translation template file (.pot), use WP-CLI:

```bash
wp i18n make-pot . languages/easy-visual-mcp.pot
```

Or use the Poedit software to scan the plugin and generate the .pot file.

## Adding Translations

1. Copy `easy-visual-mcp.pot` to `easy-visual-mcp-{locale}.po`
2. Translate all strings in the .po file
3. Generate the .mo file (usually automatic with translation tools)
4. Both .po and .mo files should be placed in this directory

Example for Spanish:
- easy-visual-mcp-es_ES.po
- easy-visual-mcp-es_ES.mo

## Translation Tools

- [Poedit](https://poedit.net/) - Desktop translation editor
- [Loco Translate](https://wordpress.org/plugins/loco-translate/) - WordPress plugin for translations
- [WP-CLI i18n](https://developer.wordpress.org/cli/commands/i18n/) - Command-line tools

## Current Status

✅ All user-facing strings are wrapped with translation functions
✅ Text domain 'easy-visual-mcp' is consistent throughout
✅ Domain path configured in plugin header
✅ load_plugin_textdomain() called on init

⏳ .pot file needs to be generated before WordPress.org submission
