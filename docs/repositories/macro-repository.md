# Macro Repository 

Resolve macros in onOffice text or HTML content.

```php
use Innobrain\OnOfficeAdapter\Facades\MacroRepository;

// Simple text
$resolved = MacroRepository::query()
    ->text('_Name, _Vorname')
    ->addressIds(1)
    ->resolve();
```

## HTML Macros
```php
$resolvedHtml = MacroRepository::query()
    ->text('<p>_Name, _Vorname</p>')
    ->isHtml()
    ->estateIds(2)
    ->resolve();
```

## Multiple Contexts
```php
$resolvedComplex = MacroRepository::query()
    ->text('_Name, _Vorname')
    ->addressIds(1)
    ->estateIds(10)
    ->agentLogIds(5)
    ->appointmentIds(7)
    ->resolve();
```

The resolved string replaces placeholders with matching data from onOffice. Great for dynamic templates or emails.