# Activity Repository

## Estates
```php
use Katalam\OnOfficeAdapter\Facades\ActivityRepository;

$activities = ActivityRepository::query()
    ->recordIdsAsAddress()
    ->recordIdsAsEstate()
    ->estate()
    ->address()
    ->recordIds([1, 2, 3])
    ->get();

$activity = ActivityRepository::query()
    ->recordIdsAsAddress()
    ->recordIdsAsEstate()
    ->estate()
    ->address()
    ->recordIds([1, 2, 3])
    ->first();

$activity = ActivityRepository::query()
    ->find(1);

ActivityRepository::query()
    ->recordIdsAsAddress()
    ->recordIdsAsEstate()
    ->estate()
    ->address()
    ->recordIds([1, 2, 3])
    ->each(function (array $estates) {
        // First page
    });

$activity = ActivityRepository::query()
    ->recordIdsAsAddress()
    ->recordIdsAsEstate()
    ->estate()
    ->address()
    ->recordIds([1, 2, 3])
    ->create([
        'activity_id' => 1,
    ]);
```
