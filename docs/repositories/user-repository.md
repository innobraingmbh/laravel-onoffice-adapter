# User Repository

Read onOffice users. The resource type is `user`.

## Querying Users

```php
use Innobrain\OnOfficeAdapter\Facades\UserRepository;

$users = UserRepository::query()->get();
$user = UserRepository::query()->first();
$user = UserRepository::query()->find(21);
```

## Selecting, Filtering & Ordering

```php
$users = UserRepository::query()
    ->select(['Name', 'Vorname', 'email'])
    ->where('Nutzungsart', 1)
    ->orderBy('Name')
    ->get();
```

## Chunked Processing

```php
UserRepository::query()->each(function (array $users) {
    // Process chunk
});
```

::: tip
`SettingRepository::users()` returns the same builder — both entry points are equivalent.
:::
