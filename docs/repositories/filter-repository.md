# Filter Repository

Using this repository, you can read out filters of both the address and estate modules.

```php
use Innobrain\OnOfficeAdapter\Facades\FilterRepository;

$result = FilterRepository::query()
    ->estate()
    ->get();

$result = FilterRepository::query()
    ->address()
    ->get();

```

::: warning
Make sure to call `estate()` or `address()` before calling `get()` or `first()` method.
:::
If you don't, you will get an `OnOfficeQueryException`:
```php
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeQueryException;

try {
    $result = FilterRepository::query()->get();
} catch (OnOfficeQueryException $e) {
    // No module specified
}
```
