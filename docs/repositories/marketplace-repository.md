# Marketplace Repository

Unlock onOffice Marketplace providers.

## Unlocking Providers

```php
use Innobrain\OnOfficeAdapter\Facades\MarketplaceRepository;

$success = MarketplaceRepository::query()
    ->unlockProvider('parameterCacheIdValue', 'extendedClaimValue');
```

| Parameter | Description |
|-----------|-------------|
| `parameterCacheId` | The parameter cache ID for the provider |
| `extendedClaim` | Extended claim for unlocking the provider |

Returns `true` on success, `false` otherwise.

See the [onOffice Marketplace documentation](https://apidoc.onoffice.de/).
