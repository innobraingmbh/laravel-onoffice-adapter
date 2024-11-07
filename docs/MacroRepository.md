# Macro Repository 

```php
use Innobrain\OnOfficeAdapter\Facades\MacroRepository;

$resolvedText = MacroRepository::query()
    ->text('_Name, _Vorname')
    ->addressIds(1)
    ->resolve();

```
