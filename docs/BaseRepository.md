# Base Repository

## Custom
```php
use Katalam\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Katalam\OnOfficeAdapter\Enums\OnOfficeAction;
use Katalam\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Katalam\OnOfficeAdapter\Facades\BaseRepository;

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
