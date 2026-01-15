# Setting Repository

The **SettingRepository** provides multiple sub-builders for different configuration resources in onOffice.

## Users

Query user information from onOffice enterprise.

```php
use Innobrain\OnOfficeAdapter\Facades\SettingRepository;

// Get all users
$users = SettingRepository::users()->get();

// Find specific user
$user = SettingRepository::users()->find(1);

// Query with filters
$users = SettingRepository::users()
    ->where('active', 1)
    ->get();

// Select specific fields
$users = SettingRepository::users()
    ->select(['Name', 'Vorname', 'email'])
    ->get();
```

::: tip
User names retrieved here can be used in address records for the `Benutzer` field (Support user).
:::

## Regions

Query region definitions for location-based filtering.

```php
// Get all regions
$regions = SettingRepository::regions()->get();

// First region
$region = SettingRepository::regions()->first();

// Chunked processing
SettingRepository::regions()
    ->each(function (array $regions) {
        foreach ($regions as $region) {
            // Process region
        }
    });
```

## Imprint

Retrieve the company imprint/impressum.

```php
$imprint = SettingRepository::imprint()->get();
```

## Actions (Aktionsart & Aktionstyp)

Query action kinds and action types for activities/agents log.

```php
$actions = SettingRepository::actions()->get();
```

::: warning
The `Aktionsart` and `Aktionstyp` fields are handled differently and must be queried via this endpoint rather than through the Field Repository.
:::

## Builder Capabilities

Each sub-builder has different capabilities:

| Builder | `get()` | `first()` | `find()` | `each()` | `count()` | Filtering | Selecting |
|---------|---------|-----------|----------|----------|-----------|-----------|-----------|
| `users()` | Yes | Yes | Yes | Yes | Yes | Yes | Yes |
| `regions()` | Yes | Yes | No | Yes | No | No | No |
| `imprint()` | Yes | Yes | Yes | No | No | No | Yes |
| `actions()` | Yes | No | No | No | No | No | No |

### Users (Full Featured)

```php
// Pagination
$users = SettingRepository::users()
    ->limit(50)
    ->offset(100)
    ->get();

// Counting
$count = SettingRepository::users()->count();

// Chunked processing
SettingRepository::users()
    ->each(function (array $users) {
        foreach ($users as $user) {
            // Process user
        }
    });
```

Refer to the [onOffice API documentation](https://apidoc.onoffice.de/) for detailed field information.
