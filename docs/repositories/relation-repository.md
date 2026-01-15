# Relation Repository

Manage relations between records in different modules. The resource type is `idsfromrelation`. Relations link data records like buyer-estate, owner-estate, contact person, etc.

## Understanding Relations

Relation types follow this scheme:
```
urn:onoffice-de-ns:smart:2.5:relationTypes:{parent}:{child}:{type}
```

For example, `urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:buyer`:
- `estate` is the parent
- `address` is the child
- `buyer` describes the relation

::: warning
Make sure you don't swap `parentids` and `childids` when querying relations.
:::

## Querying Relations

```php
use Innobrain\OnOfficeAdapter\Enums\OnOfficeRelationType;
use Innobrain\OnOfficeAdapter\Facades\RelationRepository;

// Get all contact persons for an estate
$contactPersons = RelationRepository::query()
    ->relationType(OnOfficeRelationType::ContactPersonAll)
    ->parentIds([48]) // Estate IDs
    ->get();

// Get estates for an owner (query by child ID)
$estates = RelationRepository::query()
    ->relationType(OnOfficeRelationType::Owner)
    ->childIds([1234]) // Address ID
    ->get();

// Get buyers for multiple estates
$buyers = RelationRepository::query()
    ->relationType(OnOfficeRelationType::Buyer)
    ->parentIds([48, 49, 50])
    ->get();
```

## Creating Relations

```php
// Link an address as buyer to an estate
RelationRepository::query()
    ->relationType(OnOfficeRelationType::Buyer)
    ->parentIds([48]) // Estate ID
    ->childIds([181]) // Address ID
    ->create();

// Add contact person to estate
RelationRepository::query()
    ->relationType(OnOfficeRelationType::ContactPersonAll)
    ->parentIds([48])
    ->childIds([200])
    ->create();
```

## Chunked Processing

```php
RelationRepository::query()
    ->relationType(OnOfficeRelationType::ContactPersonAll)
    ->parentIds([1, 2, 3])
    ->each(function ($relations) {
        // Relations are returned as a collection
        foreach ($relations as $id) {
            // Process each related ID
        }
    });
```

## Available Relation Types (OnOfficeRelationType Enum)

| Enum | Description | URN |
|------|-------------|-----|
| `Buyer` | Buyer of estate | `estate:address:buyer` |
| `Tenant` | Renter/tenant | `estate:address:renter` |
| `Owner` | Owner of estate | `estate:address:owner` |
| `Tipster` | Reference provider | `address:estate:tipp` |
| `ProspectiveBuyer` | Prospective buyer | `estate:address:interested` |
| `ContactPersonBroker` | Contact person (brokers) | `estate:address:contactPerson` |
| `ContactPersonAll` | All contact persons | `estate:address:contactPersonAll` |
| `EstateOfferAngebot` | Offer (Angebot) | `address:estate:offer` |
| `EstateContacted` | Contacted addresses | `address:estate:contacted` |
| `EstateMatching` | Immo-matching | `address:estate:matching` |
| `EstateOffer` | Offer by agents log | `address:estate:offerByAgentsLog` |
| `ComplexEstateUnits` | Complex units | `complex:estate:units` |
| `AddressHierarchy` | Address contacts | `address:contact:address` |

## Using Custom URNs

For relation types not in the enum, use the full URN string:

```php
RelationRepository::query()
    ->relationType('urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:buyer')
    ->parentIds([48])
    ->get();
```

## Additional Methods

```php
// Add more parent IDs
RelationRepository::query()
    ->relationType(OnOfficeRelationType::Buyer)
    ->parentIds([48])
    ->addParentIds([49, 50])
    ->get();

// Add more child IDs
RelationRepository::query()
    ->relationType(OnOfficeRelationType::Buyer)
    ->childIds([181])
    ->addChildIds([182, 183])
    ->get();
```
