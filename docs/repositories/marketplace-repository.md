# Marketplace Repository

Unlock providers in the onOffice Marketplace.

```php
use Innobrain\OnOfficeAdapter\Facades\MarketplaceRepository;

$success = MarketplaceRepository::query()
    ->unlockProvider('parameterCacheIdValue', 'extendedClaimValue');
```

- **`parameterCacheId`**: The parameter cache ID
- **`extendedClaim`**: Extended claim for unlocking the provider
- Returns `true` on success, otherwise `false`.