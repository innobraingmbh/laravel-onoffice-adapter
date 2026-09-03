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

`get()` returns a Collection keyed by parent ID => array of related child IDs, not full records. The ID arrays can contain duplicates.

## Processing All Results

The relation builder's `each()` does not chunk. It fetches all results and invokes the callback once with the full Collection.

```php
use Illuminate\Support\Collection;

RelationRepository::query()
    ->relationType(OnOfficeRelationType::ContactPersonAll)
    ->parentIds([48])
    ->each(function (Collection $relations) {
        // All relations at once
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
| `CalendarEstate` | Calendar entry linked to estate |
| `CalendarAddress` | Calendar entry linked to address |
| `TaskEstate` | Task linked to estate |
| `TaskAddress` | Task linked to address |

Custom URN: `'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:buyer'`

::: warning
The relation type determines which side is parent and which is child. The wrong direction is not an error: the API returns empty lists with a success status. For `CalendarEstate`, `CalendarAddress`, `TaskEstate`, and `TaskAddress`, the estate or address is the child.
:::
