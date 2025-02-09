# Database Structure Comparison Tool

A web-based tool for comparing the structure of MySQL databases. This tool allows users to easily identify differences between two databases including table structures, column types, and indexes.

## Features

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

## Installation

1. put the folder in your web server like /var/www/html/dbcompare

2. Ensure your web server meets these requirements:
   - PHP 7.4 or higher
   - MySQL/MariaDB
   - PDO PHP extension
   - mod_rewrite enabled (if using Apache)

3. Set up permissions:
```bash
chmod 755 /var/www/html/dbcompare
chmod 644 /var/www/html/dbcompare/*.php
```

4. Configure your web server to point to the installation directory

## Usage

1. Access the tool through your web browser:
```
http://example.com/dbcompare
```

2. The project uses your database credentials to authenticate, Enter database credentials:
   - Host
   - Username
   - Password

3. Select databases to compare:
   - Choose first database from dropdown
   - Choose second database from dropdown

4. View comparisons:
   - Summary view shows quick differences
   - Click "Detailed" for full structure comparison
   - Use dropdowns to compare specific tables
   - Use checkboxes to select multiple tables

## Security Considerations

- Store credentials securely
- This is meant more meant to run locally
- Implement access controls as needed
- Keep PHP and dependencies updated

## Contributing

Contributions are welcome

## License

This project is licensed under the MIT License - see the LICENSE file for details.
