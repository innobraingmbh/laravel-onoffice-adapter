# Macro Repository 

The macro repository is responsible for resolving macros in the onOffice API.

```php
use Innobrain\OnOfficeAdapter\Facades\MacroRepository;

$resolvedText = MacroRepository::query()
    ->text('_Name, _Vorname')
    ->addressIds(1)
    ->resolve();

```

If you want to process html, you can use the `html` method.

```php
use Innobrain\OnOfficeAdapter\Facades\MacroRepository;

$resolvedText = MacroRepository::query()
    ->text('<p>_Name, _Vorname</p>')
    ->html()
    ->estateIds(1)
    ->resolve();

```

There is even more possible sources to be added:
    
```php
use Innobrain\OnOfficeAdapter\Facades\MacroRepository;

$resolvedText = MacroRepository::query()
    ->text('_Name, _Vorname')
    ->addressIds(1)
    ->estateIds(1)
    ->agentLogIds(1)
    ->appointmentIds(1)
    ->resolve();
```
