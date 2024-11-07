# Activity Repository

## Estates
```php
use Innobrain\OnOfficeAdapter\Facades\ActivityRepository;

$activities = ActivityRepository::query()
    ->estateId(1)
    ->get();
    
$activities = ActivityRepository::query()
    ->addressIds([1, 2])
    ->get();

$activity = ActivityRepository::query()
    ->addressIds(1)
    ->first();

$activity = ActivityRepository::query()
    ->find(1);

ActivityRepository::query()
    ->addressIds([1, 2])
    ->each(function (array $estates) {
        // First page
    });

$activity = ActivityRepository::query()
    ->addressIds([1, 2, 3])
    ->create([
        'activity_id' => 1,
    ]);
```
