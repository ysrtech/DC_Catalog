# DC_Catalog — Attribute Info Pages for OpenMage / Magento 1

> **Based on the original module by [Dot Collective SRL](http://dot.collective.ro/)**  
> Original concept: *Shop by Manufacturer / Brand / Character — Attribute Info Pages*  
> Original license: [Open Software License 3.0 (OSL-3.0)](http://opensource.org/licenses/osl-3.0.php)

---

## What It Does

DC_Catalog turns any product attribute value (brand, manufacturer, colour, etc.) into a rich, SEO-friendly landing page. Each page can have its own content, banner, meta data, and custom theme — similar to a CMS page but driven by attribute data.

**Example URL:** `/brand/3m/` → branded landing page for 3M, showing filtered products with layered navigation.

---

## Features

### Core (original module)
- Attribute Info Pages for any configured product attribute
- Per-page: name, SEF URL identifier, content (WYSIWYG), banner image, logo, external URL
- Per-page meta: page title, meta keywords, meta description
- Per-page design: custom theme with date range, layout XML override
- "Favorites" ordering for highlight lists
- Frontend: filtered product grid with layered navigation, breadcrumbs, pager
- Admin grid: manage all attribute info pages from CMS menu
- Multi-store aware: separate page records per store view

### Added in this fork

#### Store View Translations
- New **"Store View Translations"** tab on every brand edit page
- All store views listed as collapsible accordion sections
- Translatable fields: **Content**, **Page Title**, **Meta Keywords**, **Meta Description**, **Description in product page**
- Each field has a **"Use Default Value"** checkbox — when ticked, the field inherits the default store's value automatically (no manual copy-paste)
- Green/orange badges show at a glance which stores already have a translation
- Translation rows are only created on demand — existing data is completely unaffected

#### Localized Attribute URL Prefixes
- New store-scoped System Config option: **Catalog → Attribute Info Pages → Attribute URL Labels**
- Map any attribute code to a translated URL prefix per store view, e.g.:
  ```
  brand=merk        (NL store)
  brand=marque      (FR store)
  brand=marke       (DE store)
  ```
- All frontend URLs (product page links, brand list, favorites, breadcrumbs) automatically use the correct prefix for the active store
- Incoming requests using the wrong prefix for a store are **301 redirected** to the correct URL (SEO-safe)
- The router resolves translated prefixes to internal attribute codes across all store configs — works even before the store is fully resolved in the request lifecycle

#### Translated Breadcrumbs & Page Titles
- Breadcrumb "All Brands" crumb uses the EAV store-specific attribute label (e.g. "Merken" on NL store) and links to the correct localized URL
- Page titles on attribute listing and detail pages use the translated label
- "Products by [Value]" title on individual brand pages
- "Shop by [Label]" title on attribute listing pages

#### Product Page Description
- **"Description in product page"** field (Product tab) is now translatable with "Use Default Value" support

---

## Installation

1. Copy the `app/` directory into your OpenMage/Magento 1 root
2. Clear the cache
3. Navigate to any admin page — the setup script will run automatically and add the required columns:
   ```sql
   ALTER TABLE catalog_attribute_page
     ADD use_default_description TINYINT(1) DEFAULT 0,
     ADD use_default_page_title TINYINT(1) DEFAULT 0,
     ADD use_default_meta_keywords TINYINT(1) DEFAULT 0,
     ADD use_default_meta_description TINYINT(1) DEFAULT 0,
     ADD use_default_description_in_page TINYINT(1) DEFAULT 0;
   ```
4. Go to **System → Configuration → Catalog → Attribute Info Pages** and select which attributes should have info pages

---

## Configuration

### Enabling Attributes
**System → Configuration → Catalog → Attribute Info Pages → Attribute Selection**  
Select which product attributes should have info pages (multiselect, global scope).

### Localized URL Prefixes
**System → Configuration → Catalog → Attribute Info Pages → Attribute URL Labels**  
Switch to a specific store view, then enter one mapping per line:
```
attribute_code=url_prefix
brand=merk
manufacturer=fabrikant
```
Leave empty to use the raw attribute code as the URL prefix (default behaviour).

---

## Translating a Brand Page

1. Open **CMS → Attribute Info Pages**
2. Click a brand to edit it
3. Go to the **"Store View Translations"** tab
4. Expand the store view you want to translate
5. Enter translated values for each field, or tick **"Use Default Value"** to inherit from the default store
6. Save — the translation row is created automatically

---

## Upgrade Notes

- **v0.2.1 → v0.2.2**: Adds 5 `TINYINT` columns to `catalog_attribute_page`. The migration is instant on InnoDB (MySQL 5.6+). All existing data and frontend behaviour is unchanged.

---

## Requirements

- OpenMage LTS or Magento 1.7+
- PHP 5.6+ / 7.x / 8.x

---

## License

[Open Software License 3.0 (OSL-3.0)](http://opensource.org/licenses/osl-3.0.php)

Original module © 2009 [Dot Collective SRL](http://dot.collective.ro)  
Additions © 2024–2026 [ysrtech](https://github.com/ysrtech)
