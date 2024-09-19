# Address Repository

## Estates
```php
use Katalam\OnOfficeAdapter\Facades\AddressRepository;

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

AddressRepository::query()
    ->addModify('address_id', 1)
    ->modify(1);

$addressCounted = AddressRepository::query()
    ->recordIds([1, 2, 3])
    ->count();

$estate = AddressRepository::query()
    ->create([
        'estate_id' => 1,
    ]);

$addresses = AddressRepository::query()
    ->setInput('foo')
    ->search();
```
