# Testing with Fake Responses

This package provides a testing API that lets you stub onOffice API responses without making real HTTP requests.

## Quick Start

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;

EstateRepository::fake(EstateRepository::response([
    EstateRepository::page(recordFactories: [
        EstateFactory::make()->id(123),
    ]),
]));

$estates = EstateRepository::query()->get();

expect($estates)->toHaveCount(1);
EstateRepository::assertSentCount(1);
```

## Response Structure

The `fake()` method accepts a nested structure that mirrors the onOffice API:

```php
Repository::fake(Repository::response([    // One response (API call)
    Repository::page(recordFactories: [     // One page of records
        Factory::make(),                    // Individual records
    ]),
]));
```

- **Response**: Represents a complete API response (can contain multiple pages)
- **Page**: A single page of results (onOffice API paginates at 500 records)
- **RecordFactories**: Individual record factories that generate test data

## Factories

### Base Methods

All factories extend `BaseFactory` and provide:

```php
// Create a factory instance
$factory = EstateFactory::make();

// Set the record ID
$factory->id(123);

// Set the record type (usually auto-configured)
$factory->type('estate');

// Set a single field
$factory->set('kaufpreis', 275000);

// Set multiple fields at once
$factory->data([
    'objekttitel' => 'Modern Apartment',
    'kaufpreis' => 275000,
]);

// Magic setter (converts setFieldName to field_name)
$factory->setKaufpreis(275000);

// Create multiple factory instances
$factories = EstateFactory::times(5);
```

## Module Examples

### Estates

Common estate fields from the onOffice API:

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;

EstateRepository::fake(EstateRepository::response([
    EstateRepository::page(recordFactories: [
        EstateFactory::make()
            ->id(3729)
            ->data([
                'objekttitel' => 'Modern Family Home in Aachen',
                'objektart' => 'haus',
                'objekttyp' => 'einfamilienhaus',
                'vermarktungsart' => 'kauf',
                'kaufpreis' => 450000.00,
                'wohnflaeche' => 185.50,
                'grundstuecksflaeche' => 520.00,
                'anzahl_zimmer' => 6,
                'anzahl_schlafzimmer' => 4,
                'anzahl_badezimmer' => 2,
                'baujahr' => 2020,
                'strasse' => 'Hauptstraße',
                'hausnummer' => '42',
                'plz' => '52068',
                'ort' => 'Aachen',
                'land' => 'DEU',
                'objektbeschreibung' => 'Beautiful family home...',
                'lage' => 'Quiet residential area...',
                'ausstattung' => 'High-quality finishes...',
                'status' => 1,
                'verkauft' => 0,
                'reserviert' => 0,
            ]),
        EstateFactory::make()
            ->id(3730)
            ->data([
                'objekttitel' => 'City Apartment with Balcony',
                'objektart' => 'wohnung',
                'vermarktungsart' => 'miete',
                'kaltmiete' => 1200.00,
                'warmmiete' => 1450.00,
                'wohnflaeche' => 75.00,
                'anzahl_zimmer' => 3,
                'plz' => '50667',
                'ort' => 'Köln',
            ]),
    ]),
]));

$estates = EstateRepository::query()
    ->select('objekttitel', 'kaufpreis', 'wohnflaeche')
    ->get();

expect($estates)->toHaveCount(2);
```

### Addresses

Common address fields:

```php
use Innobrain\OnOfficeAdapter\Facades\AddressRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\AddressFactory;

AddressRepository::fake(AddressRepository::response([
    AddressRepository::page(recordFactories: [
        AddressFactory::make()
            ->id(10505)
            ->data([
                'Anrede' => 'Herr',
                'Vorname' => 'Max',
                'Name' => 'Mustermann',
                'Zusatz1' => 'Dr.',
                'Strasse' => 'Musterstraße 123',
                'Plz' => '52068',
                'Ort' => 'Aachen',
                'Land' => 'Deutschland',
                'Briefanrede' => 'Sehr geehrter Herr Dr. Mustermann,',
                'Email' => 'max.mustermann@example.de',
                'Telefon1' => '+49 241 12345678',
                'Mobil' => '+49 170 1234567',
                'Geburtsdatum' => '1985-06-15',
                'Status' => 1,
                'Art' => 'Interessent',
            ]),
    ]),
]));

$addresses = AddressRepository::query()
    ->select('Vorname', 'Name', 'Email')
    ->get();
```

### Activities (Agents Log)

```php
use Innobrain\OnOfficeAdapter\Facades\ActivityRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\ActivityFactory;

ActivityRepository::fake(ActivityRepository::response([
    ActivityRepository::page(recordFactories: [
        ActivityFactory::make()
            ->id(67075)
            ->data([
                'Aktionsart' => 'Email',
                'Aktionstyp' => 'Ausgang',
                'Datum' => '2024-03-15 14:30:00',
                'Bemerkung' => 'Sent property exposé',
                'Objekt_nr' => ['3729'],
                'merkmal' => null,
            ]),
        ActivityFactory::make()
            ->id(67076)
            ->data([
                'Aktionsart' => 'Telefonat',
                'Aktionstyp' => 'Eingang',
                'Datum' => '2024-03-15 10:00:00',
                'Bemerkung' => 'Initial inquiry about property',
            ]),
    ]),
]));
```

### Search Criteria

```php
use Innobrain\OnOfficeAdapter\Facades\SearchCriteriaRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\SearchCriteriaFactory;

SearchCriteriaRepository::fake(SearchCriteriaRepository::response([
    SearchCriteriaRepository::page(recordFactories: [
        SearchCriteriaFactory::make()
            ->id(1001)
            ->data([
                'objektart' => ['haus', 'wohnung'],
                'vermarktungsart' => 'kauf',
                'kaufpreis__von' => 200000,
                'kaufpreis__bis' => 500000,
                'wohnflaeche__von' => 80,
                'anzahl_zimmer__von' => 3,
                'range_plz' => '5*',
                'range_land' => 'DEU',
            ]),
    ]),
]));
```

### Relations

Relations link records between modules (e.g., buyer linked to estate):

```php
use Innobrain\OnOfficeAdapter\Enums\OnOfficeRelationType;
use Innobrain\OnOfficeAdapter\Facades\RelationRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\RelationFactory;

RelationRepository::fake(RelationRepository::response([
    RelationRepository::page(recordFactories: [
        RelationFactory::make()
            ->data([
                // Estate ID => Array of related Address IDs
                5779 => ['2169', '2205'],
                5780 => ['2169'],
            ]),
    ]),
]));

$relations = RelationRepository::query()
    ->relationType(OnOfficeRelationType::EstateBuyer)
    ->parentIds([5779, 5780])
    ->get();
```

### Estate Pictures

```php
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstatePictureFactory;

EstateRepository::fake(EstateRepository::response([
    EstateRepository::page(recordFactories: [
        EstatePictureFactory::make()
            ->id(1)
            ->data([
                'url' => 'https://images.example.com/estate-1.jpg',
                'title' => 'Living Room',
                'text' => 'Spacious living room with natural light',
                'originalname' => 'living-room.jpg',
                'modified' => 1709913600,
            ]),
        EstatePictureFactory::make()
            ->id(2)
            ->data([
                'url' => 'https://images.example.com/estate-2.jpg',
                'title' => 'Kitchen',
            ]),
    ]),
]));

$pictures = EstateRepository::pictures(3729)->get();
```

### Files

```php
use Innobrain\OnOfficeAdapter\Facades\FileRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\FileFactory;

FileRepository::fake(FileRepository::response([
    FileRepository::page(recordFactories: [
        FileFactory::make()
            ->ok()  // Sets success status
            ->data([
                'tmpUploadId' => 'abc123',
            ]),
    ]),
]));

FileRepository::query()->save('abc123');
```

## Multiple Pages

Simulate paginated responses:

```php
EstateRepository::fake(EstateRepository::response([
    EstateRepository::page(recordFactories: [
        EstateFactory::make()->id(1),
        EstateFactory::make()->id(2),
    ]),
    EstateRepository::page(recordFactories: [
        EstateFactory::make()->id(3),
        EstateFactory::make()->id(4),
    ]),
]));

// The repository will automatically handle pagination
$estates = EstateRepository::query()->get();
expect($estates)->toHaveCount(4);
```

## Multiple Responses (Sequences)

For tests that make multiple API calls:

```php
EstateRepository::fake([
    // First call returns this
    EstateRepository::response([
        EstateRepository::page(recordFactories: [
            EstateFactory::make()->id(1),
        ]),
    ]),
    // Second call returns this
    EstateRepository::response([
        EstateRepository::page(recordFactories: [
            EstateFactory::make()->id(2),
        ]),
    ]),
]);

$first = EstateRepository::query()->get();  // Returns ID 1
$second = EstateRepository::query()->get(); // Returns ID 2
```

## Error Responses

Simulate API errors:

```php
use Innobrain\OnOfficeAdapter\Enums\OnOfficeError;

EstateRepository::fake(EstateRepository::response([
    EstateRepository::page(
        errorCodeResult: OnOfficeError::Unknown_Error_Occurred->value,
        messageResult: OnOfficeError::Unknown_Error_Occurred->toString(),
    ),
]));

// This will throw an exception
EstateRepository::query()->get();
```

## Assertions

### Assert Request Count

```php
EstateRepository::assertSentCount(3);
```

### Assert Request Was Sent

```php
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;

EstateRepository::assertSent(function (OnOfficeRequest $request) {
    return $request->actionId === OnOfficeAction::Read
        && $request->parameters['listlimit'] === 100;
});
```

### Assert Request Was Not Sent

```php
EstateRepository::assertNotSent(function (OnOfficeRequest $request) {
    return $request->actionId === OnOfficeAction::Create;
});
```

### Access Recorded Requests

```php
// Get all recorded request/response pairs
$recordings = EstateRepository::recorded();

// Filter recordings
$readRequests = EstateRepository::recorded(
    fn (OnOfficeRequest $request, array $response) =>
        $request->actionId === OnOfficeAction::Read
);
```

## Full Test Example

```php
<?php

use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Facades\EstateRepository;
use Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories\EstateFactory;

it('fetches active estates for sale', function () {
    // Arrange
    EstateRepository::fake(EstateRepository::response([
        EstateRepository::page(recordFactories: [
            EstateFactory::make()
                ->id(1)
                ->data([
                    'objekttitel' => 'Villa in Aachen',
                    'kaufpreis' => 850000,
                    'status' => 1,
                    'verkauft' => 0,
                ]),
            EstateFactory::make()
                ->id(2)
                ->data([
                    'objekttitel' => 'Apartment in Cologne',
                    'kaufpreis' => 320000,
                    'status' => 1,
                    'verkauft' => 0,
                ]),
        ]),
    ]));

    // Act
    $estates = EstateRepository::query()
        ->select('objekttitel', 'kaufpreis')
        ->where('status', 1)
        ->where('verkauft', 0)
        ->orderBy('kaufpreis', 'DESC')
        ->get();

    // Assert
    expect($estates)->toHaveCount(2)
        ->and($estates->first()['id'])->toBe(1);

    EstateRepository::assertSentCount(1);
    EstateRepository::assertSent(fn (OnOfficeRequest $request) =>
        $request->actionId === OnOfficeAction::Read
        && in_array('objekttitel', $request->parameters['data'])
    );
});
```

## Available Factories

| Factory | Type | Notes |
|---------|------|-------|
| `EstateFactory` | `estate` | Real estate listings |
| `AddressFactory` | `address` | Contacts, buyers, owners |
| `ActivityFactory` | `agentslog` | Activity log entries |
| `SearchCriteriaFactory` | - | Buyer search profiles |
| `RelationFactory` | - | Links between records |
| `EstatePictureFactory` | `estatepictures` | Estate images (includes defaults) |
| `FileFactory` | `file` | File uploads (has `ok()`/`error()`) |
| `FileUploadFactory` | - | Upload responses |
| `FilterFactory` | - | Saved filters |
| `FieldFactory` | - | Field configurations |
| `UserFactory` | - | User records |
| `RegionFactory` | - | Geographic regions |
| `ImprintFactory` | - | Imprint data |
| `LogFactory` | - | Log entries |
| `LinkFactory` | - | URL links |
| `LastSeenFactory` | - | Recently viewed records |
| `MarketPlaceUnlockProviderFactory` | - | Marketplace data |
| `ActionFactory` | - | Action types |

## Tips

1. **Use realistic data**: Include fields that your code actually reads to catch issues early.

2. **Test error handling**: Use error responses to verify your app handles API failures gracefully.

3. **Assert on requests**: Verify that your code sends the expected parameters to the API.

4. **Extend factories**: Create custom factories for your project's common test scenarios:

```php
class MyEstateFactory extends EstateFactory
{
    public function villa(): static
    {
        return $this->data([
            'objektart' => 'haus',
            'objekttyp' => 'villa',
            'kaufpreis' => 1500000,
        ]);
    }

    public function apartment(): static
    {
        return $this->data([
            'objektart' => 'wohnung',
            'vermarktungsart' => 'miete',
        ]);
    }
}
```
