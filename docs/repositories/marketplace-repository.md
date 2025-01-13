# Marketplace Repository

The Marketplace Repository provides functionality to interact with marketplace-related features in the onOffice API.

## Unlock Provider

Unlocks a provider using the provided parameter cache ID and extended claim.

```php
use Innobrain\OnOfficeAdapter\Facades\MarketplaceRepository;

// Unlock a provider
$success = MarketplaceRepository::query()
    ->unlockProvider('parameterCacheId', 'extendedClaim');

// Returns true if successful, false otherwise
```

### Parameters

- `parameterCacheId` (string): The parameter cache ID for the provider
- `extendedClaim` (string): The extended claim for unlocking the provider

### Returns

- `bool`: Returns `true` if the unlock was successful, `false` otherwise
