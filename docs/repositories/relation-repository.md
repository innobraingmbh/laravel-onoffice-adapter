# Relation Repository

```php
use Innobrain\OnOfficeAdapter\Enums\OnOfficeRelationType;
use Innobrain\OnOfficeAdapter\Facades\RelationRepository;

$relations = RelationRepository::query()
    ->parentIds([1, 2, 3])
    ->addParentIds(1)
    ->childIds([4, 5, 6])
    ->addChildIds(4)
    ->relationType(OnOfficeRelationType::ContactPersonAll)
    ->get();

RelationRepository::query()
    ->parentIds([1, 2, 3])
    ->addParentIds(1)
    ->childIds([4, 5, 6])
    ->addChildIds(4)
    ->relationType(OnOfficeRelationType::ContactPersonAll)
    ->each(function (array $relations) {
        // All relations, because they don't have a pagination
    });

RelationRepository::query()
    ->parentIds([1, 2, 3])
    ->addParentIds(1)
    ->childIds([4, 5, 6])
    ->addChildIds(4)
    ->relationType(OnOfficeRelationType::ContactPersonAll)
    ->create();
```

