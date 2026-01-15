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
| `ProspectiveBuyer` | Prospective buyer |
| `ContactPersonAll` | All contact persons |
| `ContactPersonBroker` | Broker contacts |
| `ComplexEstateUnits` | Complex units |

Custom URN: `'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:buyer'`

::: warning
Don't swap `parentIds` and `childIds` - the relation type determines which is which.
:::
