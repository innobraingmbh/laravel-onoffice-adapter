# Address Repository

Manage address records in onOffice. The resource type is `address`.

## Querying Addresses

```php
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;

$addresses = AddressRepository::query()->get();
$address = AddressRepository::query()->find(10505);
$addresses = AddressRepository::query()->recordIds([1, 2, 3])->get();
```

`addRecordIds()` appends to the already set record ids instead of replacing them.

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
        'Land' => 'DEU',
    ]);

// With duplicate check — pass the flag in the data array;
// parameters() has no effect on create() for addresses
$address = AddressRepository::query()
    ->create([
        'checkDuplicate' => true,
        // ...
    ]);

AddressRepository::query()
    ->addModify(['Vorname' => 'Hans'])
    ->modify(10505);
```

::: warning
`checkDuplicate` is a silent upsert: when an email in the payload matches any stored email entry of an existing record — including non-default secondary entries — that record's fields are overwritten with the payload and its ID is returned, indistinguishable from a fresh create.
:::

::: warning
`Status` accepts writes but never applies them. Archive or activate an address via `Status2Adr` with the select key (`status2adr_active` / `status2adr_archive`) — raw integers are rejected. `Status2Adr` cascades into `Status`.
:::

### Modifying Contact Details

Contact fields take flat values on `create`, but `modify` rejects them as unknown fields. Use the action-object form:

```php
AddressRepository::query()
    ->addModify('email', [
        'action' => 'modify', // add, modify, or delete
        'oldvalue' => 'old@example.de',
        'newvalue' => 'new@example.de',
        'default' => true,
    ])
    ->modify(10505);
```

Reads only expose the default entry — an entry added without `'default' => true` is invisible to every subsequent read.

### Contact Parameters

| Parameter | Description |
|-----------|-------------|
| `phone` | Phone entries |
| `mobile` | Mobile phone |
| `fax` | Fax entries |
| `email` | Email entries |
| `defaultphone` / `defaultemail` | Main number/email |

## Files, Count & Chunked

```php
$files = AddressRepository::files(100)->get();
$file = AddressRepository::files(100)->first();
$file = AddressRepository::files(100)->find(12);

AddressRepository::files(100)
    ->addModify('Art', 'Dokument')
    ->modify(12);

AddressRepository::files(100)->delete(12);

AddressRepository::files(100)->each(fn ($files) => /* process */);

$count = AddressRepository::query()->where('Status', 1)->count();

AddressRepository::query()->each(fn ($addresses) => /* process */);
```

## Common Fields

| Field | Description |
|-------|-------------|
| `Status` | 1 = Active, 0 = Archive — read-only, write `Status2Adr` |
| `Status2Adr` | `status2adr_active` / `status2adr_archive` — the writable status |
| `Anrede` | Salutation |
| `Vorname` / `Name` | First/last name |
| `Strasse` / `Plz` / `Ort` / `Land` | Address |
| `Benutzer` | Support user — takes the login name, not the numeric user ID |
| `newsletter_aktiv` | 0=No, 1=Yes, 2=Cancelled, 3=DOI pending, 4=Unspecified, 5=Undeliverable |
