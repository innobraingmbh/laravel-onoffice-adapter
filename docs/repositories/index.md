# Repositories

Repositories are the primary entry point for retrieving and modifying data in onOffice. Each repository corresponds to a specific resource (Estates, Addresses, Activities, etc.). This page provides a quick overview of the available repositories.

## Available Repositories
1. [Activity](./activity-repository.md)
2. [Address](./address-repository.md)
3. [Base](./base-repository.md)
4. [Estate](./estate-repository.md)
5. [Field](./field-repository.md)
6. [File](./file-repository.md)
7. [Filter](./filter-repository.md)
8. [Last Seen](./last-seen-repository.md)
9. [Link](./link-repository.md)
10. [Log](./log-repository.md)
11. [Macro](./macro-repository.md)
12. [Marketplace](./marketplace-repository.md)
13. [Relation](./relation-repository.md)
14. [Search Criteria](./search-criteria-repository.md)
15. [Setting](./setting-repository.md)

## Usage Example

```php
$estates = EstateRepository::query()
    ->where('objektart', 'buero_praxen')
    ->whereIn('estate_id', [1, 2, 3])
    ->get();

$count = EstateRepository::query()
    ->whereBetween('kaufpreis', 100000, 200000)
    ->count();
```

::: tip
See each repository’s dedicated page for advanced operations such as create, modify, chunking, and special parameters.
:::

Check out the docs for each repository to see usage examples and best practices.
