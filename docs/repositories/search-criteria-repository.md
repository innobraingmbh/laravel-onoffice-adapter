# Search Criteria Repository

```php
use Innobrain\OnOfficeAdapter\Facades\SearchCriteriaRepository;

$searchCriteria = SearchCriteriaRepository::query()
    ->mode('internal')
    ->find(1);
```

