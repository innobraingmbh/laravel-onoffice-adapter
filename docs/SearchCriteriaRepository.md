# Search Criteria Repository

```php
use Katalam\OnOfficeAdapter\Facades\SearchCriteriaRepository;

$searchCriteria = SearchCriteriaRepository::query()
    ->mode('internal')
    ->find(1);
```

