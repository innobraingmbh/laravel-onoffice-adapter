# Address Repository

Manage address records in onOffice. The resource type is `address`.

## Querying Addresses

```php
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;

$addresses = AddressRepository::query()->get();
$address = AddressRepository::query()->find(10505);
$addresses = AddressRepository::query()->recordIds([1, 2, 3])->get();
```

::: warning
Record number (Datensatznummer) and customer number (KdNr) are different. The record number is the API ID.
:::

## Selecting Fields

```php
$addresses = AddressRepository::query()
    ->select(['Vorname', 'Name', 'Strasse', 'Plz', 'Ort'])
    ->get();

// Contact details: phone, mobile, fax, email, defaultphone, defaultemail, imageUrl
```

## Filtering

```php
$addresses = AddressRepository::query()
    ->where('Status', 1)
    ->whereIn('Vorname', ['Max', 'Moritz'])
    ->whereBetween('letzter_Kontakt', '2024-01-01', '2024-12-31')
    ->whereLike('Email', '%@example.com')
    ->get();
```

## Search & Sort

```php
$addresses = AddressRepository::query()
    ->setInput('Mustermann')
    ->search();

$addresses = AddressRepository::query()
    ->orderBy('Name')
    ->addCountryIsoCodeType('ISO-3166-3')
    ->get();
```

## Create & Modify

```php
$address = AddressRepository::query()
    ->create([
        'Anrede' => 'Herr',
        'Vorname' => 'Max',
        'Name' => 'Mustermann',
        'email' => 'm.mustermann@example.de',
        'phone' => '0241 12345',
        'default_phone' => '0241 12345',
        'Land' => 'DEU',
    ]);

// With duplicate check
$address = AddressRepository::query()
    ->parameters(['checkDuplicate' => true])
    ->create([...]);

AddressRepository::query()
    ->addModify(['Vorname' => 'Hans', 'Status' => 1])
    ->modify(10505);
```

### Contact Parameters

| Parameter | Description |
|-----------|-------------|
| `phone` / `phone_private` / `phone_business` | Phone entries |
| `mobile` | Mobile phone |
| `fax` / `fax_private` / `fax_business` | Fax entries |
| `email` / `email_private` / `email_business` | Email entries |
| `default_phone` / `default_email` | Set main number/email |

## Files, Count & Chunked

```php
$files = AddressRepository::files(100)->get();
AddressRepository::files(100)->delete(12);

$count = AddressRepository::query()->where('Status', 1)->count();

AddressRepository::query()->each(fn ($addresses) => /* process */);
```

## Common Fields

| Field | Description |
|-------|-------------|
| `Status` | 1 = Active, 0 = Archive |
| `Anrede` | Salutation |
| `Vorname` / `Name` | First/last name |
| `Strasse` / `Plz` / `Ort` / `Land` | Address |
| `Benutzer` | Support user |
| `newsletter_aktiv` | 0=No, 1=Yes, 2=Cancelled, 3=DOI pending |
