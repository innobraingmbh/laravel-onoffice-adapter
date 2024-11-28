# Setting Repository

## Users
```php
use Innobrain\OnOfficeAdapter\Facades\SettingRepository;

$settings = SettingRepository::users()
    ->get();

$setting = SettingRepository::users()
    ->first();

$setting = SettingRepository::users()
    ->find(1);

SettingRepository::users()
    ->each(function (array $settings) {
        // First page
    });

$count = SettingRepository::users()
    ->count();
```

## Regions
```php
use Innobrain\OnOfficeAdapter\Facades\SettingRepository;

$settings = SettingRepository::regions()
    ->get();
```

## Imprint
```php
use Innobrain\OnOfficeAdapter\Facades\SettingRepository;

$settings = SettingRepository::imprint()
    ->get();
```

## Actions
```php
use Innobrain\OnOfficeAdapter\Facades\SettingRepository;

$settings = SettingRepository::actions()
    ->get();
```

