# Macro Repository

Resolve placeholders in text using onOffice record data.

## Usage

```php
use Innobrain\OnOfficeAdapter\Facades\MacroRepository;

$text = MacroRepository::query()
    ->text('Dear _Anrede _Name,')
    ->addressIds(1234)
    ->resolve();

$html = MacroRepository::query()
    ->text('<p>Property: _objekttitel, Price: _kaufpreis</p>')
    ->isHtml()
    ->estateIds(100)
    ->resolve();

// Multiple contexts
$text = MacroRepository::query()
    ->text('Dear _Anrede _Name, regarding _objekttitel...')
    ->addressIds(1234)
    ->estateIds(100)
    ->resolve();
```

## Context Methods

| Method | Description |
|--------|-------------|
| `addressIds(int\|array)` | Address macros |
| `estateIds(int\|array)` | Estate macros |
| `agentLogIds(int\|array)` | Activity macros |
| `appointmentIds(int\|array)` | Appointment macros |

## Common Macros

**Address**: `_Anrede`, `_Vorname`, `_Name`, `_Briefanrede`, `_Strasse`, `_Plz`, `_Ort`

**Estate**: `_objekttitel`, `_kaufpreis`, `_warmmiete`, `_wohnflaeche`, `_ort`
