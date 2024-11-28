# Activity Repository

You can easily query and create activities with the ActivityRepository.

## Query

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

$count = ActivityRepository::query()
    ->addressIds([1, 2])
    ->count();
```

## Create
```php
$activity = ActivityRepository::query()
    ->addressIds([1, 2, 3])
    ->create([
        'note' => 'This is a note',
        'datetime' => '2021-02-08 11:13:30'
    ]);
```
