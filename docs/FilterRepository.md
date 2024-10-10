# Filter Repository

```php
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeQueryException;
use Innobrain\OnOfficeAdapter\Facades\FilterRepository;

$result = FilterRepository::query()
    ->estate()
    ->get();

$result = FilterRepository::query()
    ->address()
    ->get();

try {
    $result = FilterRepository::query()->get();
} catch (OnOfficeQueryException $e) {
    // No module specified
}
```

