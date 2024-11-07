# Base Repository

Whenever there is an endpoint that is not covered by any of the current available repositories, you can use the `BaseRepository` to make custom requests.

::: tip
The `BaseRepository` is a generic repository that can be used to make custom requests to the OnOffice API.
Using this repository will make sure that you are compatible with future versions of this package.
Whenever there is a repository supporting your usecase, you will be able to switch to that with ease ✨
:::

## Making Requests

To make a request, initialize the QueryBuilder with the `query` method and chain the methods you need to make the request.
Specifically, you will have to craft your own OnOfficeRequest object and pass it to the `call` method.

Typically, it will look like this:

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

```

If the `ResourceType` is not yet available in the package, you can pass a string instead.
You can find the correct string in the OnOffice API documentation.

```php
$collection = BaseRepository::query()
    ->call(new OnOfficeRequest(
        OnOfficeAction::Read,
        'estate'
    ));
```

### Further available methods
Call only once:
```php
$first = BaseRepository::query()
    ->call(new OnOfficeRequest(
        OnOfficeAction::Read,
        OnOfficeResourceType::Estate,
    ))
    ->once();
```

Chunk over pages and do things with them:
```php
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
