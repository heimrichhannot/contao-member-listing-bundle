# Changelog

All notable changes to this project will be documented in this file.

## [0.1.3] - 2025-07-10
- Fixed: compatibility with migration to Contao 5
  - If data was migrated from Contao 4, and migrations didn't run with deletes, the
    remaining addImage column was treated as authoritative, albeit it was removed in Contao 5.

## [0.1.2] - 2024-12-09
- Changed: adjusted template and controller for a nicer backend view
- Fixed: element name not correctly displayed in backend

## [0.1.1] - 2024-12-03
- Added: json-ld support
- Added: support for member singleSRC field
- Added: more templates

## [0.1.0] - 2024-09-19
Initial version.