# Search Criteria Repository

## Find

```php
use Innobrain\OnOfficeAdapter\Facades\SearchCriteriaRepository;

$searchCriteria = SearchCriteriaRepository::query()
    ->mode('internal')
    ->find(1);
```

## Create

```php

use Innobrain\OnOfficeAdapter\Facades\SearchCriteriaRepository;

$searchCriteria = SearchCriteriaRepository::query()
    ->addressId(1214)
    ->create([]);
```

