# Repositories

Repositories are the primary entry point for retrieving and modifying data in onOffice. Each repository corresponds to a specific resource (Estates, Addresses, Activities, etc.). This page provides a quick overview of the available repositories.

## Available Repositories
1. [Action](./action-repository.md)
2. [Activity](./activity-repository.md)
3. [Address](./address-repository.md)
4. [Appointment](./appointment-repository.md)
5. [Base](./base-repository.md)
6. [Batch](./batch-repository.md)
7. [Estate](./estate-repository.md)
8. [Field](./field-repository.md)
9. [File](./file-repository.md)
10. [Filter](./filter-repository.md)
11. [Last Seen](./last-seen-repository.md)
12. [Link](./link-repository.md)
13. [Log](./log-repository.md)
14. [Macro](./macro-repository.md)
15. [Marketplace](./marketplace-repository.md)
16. [Relation](./relation-repository.md)
17. [Search Criteria](./search-criteria-repository.md)
18. [Setting](./setting-repository.md)
19. [Task](./task-repository.md)
20. [User](./user-repository.md)

## Usage Example

```php
$estates = EstateRepository::query()
    ->where('objektart', 'buero_praxen')
    ->whereIn('Id', [1, 2, 3])
    ->get();

$count = EstateRepository::query()
    ->whereBetween('kaufpreis', 100000, 200000)
    ->count();
```

::: tip
See each repository’s dedicated page for advanced operations such as create, modify, chunking, and special parameters.
:::

Check out the docs for each repository to see usage examples and best practices.
