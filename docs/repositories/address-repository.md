# Address Repository

## Query

```php
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;

$addresses = AddressRepository::query()
    ->addCountryIsoCodeType('DE')
    ->recordIds([1, 2, 3])
    ->get();

$address = AddressRepository::query()
    ->addCountryIsoCodeType('DE')
    ->recordIds([1, 2, 3])
    ->first();

$address = AddressRepository::query()
    ->addCountryIsoCodeType('DE')
    ->find(1);

AddressRepository::query()
    ->addCountryIsoCodeType('DE')
    ->recordIds([1, 2, 3])
    ->each(function (array $estates) {
        // First page
    });

```

## Modify

```php
AddressRepository::query()
    ->addModify('Vorname', 'Hans')
    ->modify();
```

## Count

```php
$addressCounted = AddressRepository::query()
    ->recordIds([1, 2, 3])
    ->count();
```

## Create

```php
$estate = AddressRepository::query()
    ->create([
        'Vorname' => 'Hans',
    ]);
```

## Search

::: tip
You can search using a search term. If you want to further restrict the search results, you can use the `where` method.
:::

```php
// Search with filters
$addresses = AddressRepository::query()
    ->setInput('foo')
    ->where('Vorname', 'like', 'Hans%')
    ->search();
```
