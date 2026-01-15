# Macro Repository

Resolve macros/placeholders in text or HTML content using data from onOffice records. Macros for estates, addresses, appointments, and agents log can be resolved.

## Basic Usage

```php
use Innobrain\OnOfficeAdapter\Facades\MacroRepository;

// Resolve address macros
$resolved = MacroRepository::query()
    ->text('Dear _Anrede _Name _Vorname,')
    ->addressIds(1234)
    ->resolve();
// Output: "Dear Herr Mustermann Max,"
```

## HTML Content

For HTML templates, mark the content as HTML:

```php
$resolvedHtml = MacroRepository::query()
    ->text('<p>Property: _objekttitel</p><p>Price: _kaufpreis</p>')
    ->isHtml()
    ->estateIds(100)
    ->resolve();
```

## Multiple Contexts

Combine data from multiple record types:

```php
$resolved = MacroRepository::query()
    ->text('Dear _Anrede _Name, regarding property _objekttitel...')
    ->addressIds(1234)
    ->estateIds(100)
    ->resolve();
```

## Available Context Methods

| Method | Description |
|--------|-------------|
| `addressIds(int\|array)` | Address record(s) for address macros |
| `estateIds(int\|array)` | Estate record(s) for estate macros |
| `agentLogIds(int\|array)` | Agents log record(s) for activity macros |
| `appointmentIds(int\|array)` | Appointment record(s) for calendar macros |

## Common Macro Patterns

Macros follow the pattern `_fieldname` where `fieldname` is the internal field name from onOffice.

### Address Macros

| Macro | Description |
|-------|-------------|
| `_Anrede` | Salutation |
| `_Vorname` | First name |
| `_Name` | Last name |
| `_Briefanrede` | Letter salutation |
| `_Strasse` | Street |
| `_Plz` | Postal code |
| `_Ort` | City |

### Estate Macros

| Macro | Description |
|-------|-------------|
| `_objekttitel` | Property title |
| `_kaufpreis` | Purchase price |
| `_warmmiete` | Warm rent |
| `_wohnflaeche` | Living area |
| `_ort` | City |
| `_objektart` | Property type |

## Use Cases

The Macro Repository is ideal for:
- Email templates with personalized content
- PDF exposé generation
- Dynamic document generation
- Mail merge functionality

::: tip
Use the Field Repository to discover available field names for each module. The macro name is typically the field name prefixed with underscore.
:::
