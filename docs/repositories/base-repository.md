# Base Repository

## Custom
```php
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\BaseRepository;

$collection = BaseRepository::query()
    ->call(new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
    ));

$first = BaseRepository::query()
    ->call(new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
    ))
    ->once();

BaseRepository::query()
    ->chunked(
        new OnOfficeRequest(
            OnOfficeAction::Read,
            OnOfficeResourceType::Estate,
        ),
        function (array $estates) {
            // First page
        },
    );
```

## dd, dump
```php
use Innobrain\OnOfficeAdapter\Facades\BaseRepository;

BaseRepository::query()
    ->dd()
    ->call(new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
    ));

// → will dump the request and die
```
```php
use Innobrain\OnOfficeAdapter\Facades\BaseRepository;

BaseRepository::query()
    ->dump()
    ->call(new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
    ));

// → will dump the request
```

```php
use Innobrain\OnOfficeAdapter\Facades\BaseRepository;

BaseRepository::query()
    ->raw()
    ->call(new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
    ));

// → will dump the request exactly as fired and die
```

```php
use Innobrain\OnOfficeAdapter\Facades\BaseRepository;

BaseRepository::record();

BaseRepository::query()
    ->call(new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
    ));
    
$result = BaseRepository::lastRecorded();

/*
    $result = [
        OnOfficeRequest,
        OnOfficeResponse,
    ];
*/
```
```php
use Innobrain\OnOfficeAdapter\Facades\BaseRepository;

BaseRepository::record();

BaseRepository::query()
    ->call(new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
    ));
    
$result = BaseRepository::lastRecordedRequest();

/*
    $result = OnOfficeRequest;
*/
```
```php
use Innobrain\OnOfficeAdapter\Facades\BaseRepository;

BaseRepository::record();

BaseRepository::query()
    ->call(new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
    ));
    
$result = BaseRepository::lastRecordedResponse();

/*
    $result = OnOfficeResponse;
*/
```
