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
