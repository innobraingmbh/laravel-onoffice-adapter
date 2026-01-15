# Address Repository

Manage address records in onOffice. The resource type is `address`.

## Querying Addresses

```php
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;

// Basic query
$addresses = AddressRepository::query()
    ->get();

// Find a single address by ID (record number / Datensatznummer)
$address = AddressRepository::query()
    ->find(10505);

// Query specific addresses
$addresses = AddressRepository::query()
    ->recordIds([1, 2, 3])
    ->get();
```

::: warning
The record number (Datensatznummer) and customer number (Kundennummer/KdNr) are different fields. The record number is the ID used for API calls.
:::

## Selecting Fields

```php
$addresses = AddressRepository::query()
    ->select([
        'Briefanrede',
        'Vorname',
        'Name',
        'Land',
        'Ort',
        'Plz',
        'Strasse',
    ])
    ->get();

// Contact details
$addresses = AddressRepository::query()
    ->select([
        'phone',        // All phone entries except mobile
        'mobile',       // Mobile numbers only
        'fax',          // All fax entries
        'email',        // All email entries
        'defaultphone', // Default phone only
        'defaultemail', // Default email only
        'imageUrl',     // Passport photo URL
    ])
    ->recordIds([10891])
    ->get();
```

## Filtering

```php
// Filter by name
$addresses = AddressRepository::query()
    ->whereIn('Vorname', ['Max', 'Moritz'])
    ->get();

// Filter by date range
$addresses = AddressRepository::query()
    ->whereBetween('letzter_Kontakt', '2024-01-01 00:00:00', '2024-12-31 15:00:00')
    ->get();

// Filter by status
$addresses = AddressRepository::query()
    ->where('Status', 1) // 1 = Active, 0 = Archive
    ->get();

// Filter by email (searches main email only)
$addresses = AddressRepository::query()
    ->whereLike('Email', '%@example.com')
    ->get();
```

::: tip
Filtering by `Email` or `Telefon1` only searches the main number/email. Use `phone`, `email`, or `fax` fields to search all entries.
:::

## Country ISO Code

```php
$addresses = AddressRepository::query()
    ->addCountryIsoCodeType('ISO-3166-3') // or 'ISO-3166-2'
    ->select(['Name', 'Land'])
    ->get();
```

## Searching

```php
$searchedAddresses = AddressRepository::query()
    ->setInput('Mustermann') // Search term
    ->where('Land', 'DEU')
    ->search();
```

## Sorting

```php
$addresses = AddressRepository::query()
    ->orderBy('Strasse')
    ->orderByDesc('Name')
    ->get();
```

## Creating Addresses

```php
$newAddress = AddressRepository::query()
    ->create([
        'Anrede' => 'Herr',
        'Vorname' => 'Max',
        'Name' => 'Mustermann',
        'email' => 'm.mustermann@example.de',
        'phone' => '0241 12345',
        'phone_business' => '0241 56789',
        'default_phone' => '0241 12345', // Must match one of the phone values
        'Plz' => '52074',
        'Ort' => 'Aachen',
        'Land' => 'DEU', // Or full name like 'Deutschland'
        'Benutzer' => 'theotest', // Support user name
        'HerkunftKontakt' => ['Suchmaschine', 'Newsletter'],
    ]);
```

### Contact Details Parameters

| Parameter | Description |
|-----------|-------------|
| `phone` | Normal phone entry |
| `phone_private` | Phone with type "private" |
| `phone_business` | Phone with type "business" |
| `mobile` | Phone with type "mobile" |
| `default_phone` | Sets the main phone number |
| `fax` | Normal fax entry |
| `fax_private` | Fax with type "private" |
| `fax_business` | Fax with type "business" |
| `email` | Normal email entry |
| `email_private` | Email with type "private" |
| `email_business` | Email with type "business" |
| `default_email` | Sets the main email address |

### Custom Parameters

For API features not directly exposed by the builder, use `parameters()`:

```php
// Duplicate check
$address = AddressRepository::query()
    ->parameters([
        'checkDuplicate' => true,
        'noOverrideByDuplicate' => true,
    ])
    ->create([
        'email' => 'existing@example.de',
        'Vorname' => 'New',
        'Name' => 'Contact',
    ]);

// Using a predefined filter from enterprise
$addresses = AddressRepository::query()
    ->parameters(['filterid' => 102])
    ->get();
```

## Modifying Addresses

```php
// Single field
AddressRepository::query()
    ->addModify('Vorname', 'Hans')
    ->modify(10505);

// Multiple fields
AddressRepository::query()
    ->addModify('Vorname', 'Hans')
    ->addModify('Name', 'Schmidt')
    ->addModify('Status', 1)
    ->modify(10505);
```

## Address Files

```php
// Retrieve files
$files = AddressRepository::files(100)->get();

// Delete a file
AddressRepository::files(100)->delete(12);
```

## Counting

```php
$count = AddressRepository::query()
    ->where('Land', 'DEU')
    ->where('Status', 1)
    ->count();
```

## Chunked Processing

```php
AddressRepository::query()
    ->where('Status', 1)
    ->each(function (array $addresses) {
        foreach ($addresses as $address) {
            // Process each address
        }
    });
```

## Common Field Names

| Field | Description |
|-------|-------------|
| `Status` | 1 = Active, 0 = Archive |
| `Anrede` | Salutation (Herr, Frau, etc.) |
| `Vorname` | First name |
| `Name` | Last name |
| `Briefanrede` | Letter salutation |
| `Strasse` | Street |
| `Plz` | Postal code |
| `Ort` | City |
| `Land` | Country |
| `Benutzer` | Support user |
| `Aenderung` | Last modified date |
| `newsletter_aktiv` | Newsletter status (0=No, 1=Yes, 2=Cancelled, 3=DOI pending, 4=Not specified) |

For detailed parameters, refer to the [onOffice API documentation](https://apidoc.onoffice.de/).
