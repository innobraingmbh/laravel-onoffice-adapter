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

// Duplicate check. The flag goes in the data array;
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
`checkDuplicate` is a silent upsert. When an email in the payload matches any stored email entry of an existing record, including secondary entries, that record is overwritten with the payload and its ID is returned. The response is identical to a fresh create.
:::

::: warning
`Status` accepts writes but never applies them. Archive or activate an address via `Status2Adr` with the select key `status2adr_active` or `status2adr_archive`. Raw integers are rejected. `Status2Adr` cascades into `Status`.
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

Reads only expose the default entry. An entry added without `'default' => true` never appears in a read.

### Contact Parameters

| Parameter | Description |
|-----------|-------------|
| `phone` | Phone entries |
| `mobile` | Mobile phone |
| `fax` | Fax entries |
| `email` | Email entries |
| `defaultphone` / `defaultemail` | Main number/email |

## Hierarchy Contacts

Read the addresses to contact within the contact hierarchy of a parent address. The resource type is `addressHierarchyContacts`.

```php
$contacts = AddressRepository::hierarchyContacts(691)->get();
$contact = AddressRepository::hierarchyContacts(691)->first();
```

Which addresses come back depends on the parent's `receiverSelection` (Empfänger-Auswahl), not on the hierarchy alone:

| `receiverSelection` | Returned |
|---------------------|----------|
| `ownAdress` | Only the address itself |
| `ownAdressAndRelations` | The address and its subordinate addresses |
| `onlyRelations` | Only the subordinate addresses |

Deeper levels follow their own `receiverSelection`: the subordinates of a subordinate address are only returned when that address includes them too.

```php
[
    ['id' => 691, 'type' => 'address', 'elements' => ['id' => 691, 'customerno' => '599', 'firstname' => '', 'surname' => '', 'company' => 'Mufflon GmbH', 'primaryEmail' => 'info@my-onoffice.com']],
    ['id' => 693, 'type' => 'address', 'elements' => ['id' => 693, 'customerno' => '600', 'firstname' => 'Geralinde', 'surname' => 'Mufflon', 'company' => 'Mufflon GmbH', 'primaryEmail' => 'g.mufflon@my-onoffice.com']],
]
```

`get()` reads all contacts in a single request. There is no pagination and no `each()`. The `urn:onoffice-de-ns:smart:2.5:relationTypes:address:contact:address` relation holds the personal main and secondary contacts, not this hierarchy.

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
| `Status` | 1 = Active, 0 = Archive. Read-only; write `Status2Adr` |
| `Status2Adr` | `status2adr_active` / `status2adr_archive`. The writable status |
| `Anrede` | Salutation |
| `Vorname` / `Name` | First/last name |
| `Strasse` / `Plz` / `Ort` / `Land` | Address |
| `Benutzer` | Support user. Takes the login name, not the numeric user ID |
| `newsletter_aktiv` | 0=No, 1=Yes, 2=Cancelled, 3=DOI pending, 4=Unspecified, 5=Undeliverable |
