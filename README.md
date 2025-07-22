# COBALT (Compare, Browse, Access, List Tools)

A web-based tool for exploring and comparing MySQL databases. COBALT allows you to browse databases, view tables, columns, and indexes, and easily identify differences between two databases including table structures, column types, and indexes.

## Features

- Browse all databases and tables in a hierarchical, collapsible view
- View table columns and indexes in detail (phpMyAdmin style)
- Live comparison of two database structures
- Visual indicators for differences:
  - ✓ Identical structures (green)
  - ≠ Type mismatches (yellow)
  - ! Missing tables/columns (red)
- Interactive comparison features:
  - Quick summary view
  - Detailed comparison view
  - Table-by-table comparison
  - Dropdown to compare with any table
- Comparison details include:
  - Column types and differences
  - Missing columns
  - Index differences
  - Structure mismatches
- Batch selection for multiple table comparisons
- Session-based credentials management
- Responsive Bootstrap-based UI
- Multi-language/localized interface

## Installation

1. Put the folder in your web server, e.g. `/var/www/html/cobalt`

2. Ensure your web server meets these requirements:
   - PHP 7.4 or higher
   - MySQL/MariaDB
   - PDO PHP extension
   - mod_rewrite enabled (if using Apache)

3. Set up permissions:
```bash
chmod 755 /var/www/html/cobalt
chmod 644 /var/www/html/cobalt/*.php
```

4. Configure your web server to point to the installation directory

## Usage

1. Access the tool through your web browser:
```
http://example.com/cobalt
```

2. Enter your database credentials:
   - Host
   - Username
   - Password

3. Browse and explore:
   - Expand/collapse databases and categories in the sidebar
   - Click a table to view its columns and indexes
   - Click a database to view its summary and tables

4. Compare databases:
   - Choose first and second database from dropdowns
   - View summary or detailed comparison
   - Use dropdowns to compare specific tables
   - Use checkboxes to select multiple tables

## Security Considerations

- Store credentials securely
- This is meant to run locally or in a trusted environment
- Implement access controls as needed
- Keep PHP and dependencies updated

## Contributing

Contributions are welcome

## License

This project is licensed under the MIT License - see the LICENSE file for details.
