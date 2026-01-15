# Marketplace Repository

Unlock onOffice Marketplace providers.

## Unlocking Providers

Unlock a marketplace provider for use.

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

## Usage Notes

The Marketplace API is primarily used for third-party integrations with onOffice enterprise.

For detailed marketplace integration documentation, refer to the [onOffice Marketplace documentation](https://apidoc.onoffice.de/).
