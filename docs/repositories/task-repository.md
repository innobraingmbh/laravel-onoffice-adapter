# Task Repository

Manage tasks in onOffice. The resource type is `task`.

## Querying Tasks

```php
use Innobrain\OnOfficeAdapter\Facades\TaskRepository;

$tasks = TaskRepository::query()->get();
$task = TaskRepository::query()->first();
$task = TaskRepository::query()->find(5);
```

## Selecting & Filtering

```php
$tasks = TaskRepository::query()
    ->select(['Betreff', 'Status', 'Prio', 'Verantwortung'])
    ->where('Status', 1)
    ->get();
```

## Filtering by Related Records

Tasks can be filtered by the address, estate, or project they belong to:

```php
$tasks = TaskRepository::query()->relatedAddress(42)->get();
$tasks = TaskRepository::query()->relatedEstate(99)->get();
$tasks = TaskRepository::query()->relatedProject(7)->get();
```

## Creating Tasks

The related record methods also work for create and modify — the ids are sent alongside the task data:

```php
TaskRepository::query()
    ->relatedAddress(7)
    ->relatedEstate(42)
    ->create([
        'Betreff' => 'Call back',
        'Prio' => 3,
    ]);
```

## Modifying Tasks

```php
TaskRepository::query()
    ->addModify('Status', 4)
    ->modify(99);
```

## Count & Chunked

```php
$count = TaskRepository::query()->relatedEstate(42)->count();

TaskRepository::query()->each(function (array $tasks) {
    // Process chunk
});
```

::: warning
The task endpoint reports `cntabsolute` as the number of returned rows, not a true total. `count()` works around this by requesting the maximum page size, so it is capped at 500 — more matching tasks than that still return 500.
:::
