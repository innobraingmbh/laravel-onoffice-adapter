# Relation Repository

Manage relations between records. The resource type is `idsfromrelation`.

## Querying

```php
use Innobrain\OnOfficeAdapter\Enums\OnOfficeRelationType;
use Innobrain\OnOfficeAdapter\Facades\RelationRepository;

// Get contact persons for estate
$contacts = RelationRepository::query()
    ->relationType(OnOfficeRelationType::ContactPersonAll)
    ->parentIds([48])
    ->get();

// Get estates for owner
$estates = RelationRepository::query()
    ->relationType(OnOfficeRelationType::Owner)
    ->childIds([1234])
    ->get();
```

`addParentIds()` and `addChildIds()` append to the already set ids instead of replacing them.

## Chunked Processing

```php
RelationRepository::query()
    ->relationType(OnOfficeRelationType::ContactPersonAll)
    ->parentIds([48])
    ->each(function (array $relations) {
        // Process chunk
    });
```

## Creating

```php
RelationRepository::query()
    ->relationType(OnOfficeRelationType::Buyer)
    ->parentIds([48])
    ->childIds([181])
    ->create();
```

## Relation Types

| Enum | Description |
|------|-------------|
| `Buyer` | Buyer of estate |
| `Tenant` | Renter/tenant |
| `Owner` | Owner of estate |
| `Tipster` | Tipster for estate |
| `ProspectiveBuyer` | Prospective buyer |
| `ContactPersonAll` | All contact persons |
| `ContactPersonBroker` | Broker contacts |
| `EstateOfferAngebot` | Estates offered to address |
| `EstateContacted` | Estates the address contacted |
| `EstateMatching` | Estates matching the address |
| `EstateOffer` | Estate offers from agents log |
| `ComplexEstateUnits` | Complex units |
| `AddressHierarchy` | Contact address hierarchy |

Custom URN: `'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:buyer'`

::: warning
Don't swap `parentIds` and `childIds` - the relation type determines which is which.
:::
