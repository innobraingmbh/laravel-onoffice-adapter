# Estate Repository

## Estates

### Query
```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

$estates = EstateRepository::query()
    ->get();

$estate = EstateRepository::query()
    ->first();

$estate = EstateRepository::query()
    ->find(1);

EstateRepository::query()
    ->each(function (array $estates) {
        // First page
    });

```

### Modify

```php
EstateRepository::query()
    ->addModify('objektart', 'haus')
    ->modify();

```

### Create

```php
$estate = EstateRepository::query()
    ->create([
        'objektart' => 'haus',
    ]);
```

### Search

::: tip
You can search using a search term. If you want to further restrict the search results, you can use the `where` method.
:::

```php
EstateRepository::query()
    ->search('Karmelitenstr.')
    ->where('objektart', 'haus')
    ->get();
```

## Estate Files
```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

$files = EstateRepository::files(1)
    ->get();

$file = EstateRepository::files(1)
    ->first();

$file = EstateRepository::files(1)
    ->find(1);

EstateRepository::files(1)
    ->each(function (array $files) {
        // First page
    });

EstateRepository::files(1)
    ->addModify('file_id', 1)
    ->modify(1);

EstateRepository::files(1)
    ->delete(1);
```
