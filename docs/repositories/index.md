# Repositories

Repositories are the primary entry point for retrieving and modifying data in onOffice. Each repository corresponds to a specific resource (Estates, Addresses, Activities, etc.). This page provides a quick overview of the available repositories.

## Available Repositories
1. [Action](./action-repository.md)
2. [Activity](./activity-repository.md)
3. [Address](./address-repository.md)
4. [Appointment](./appointment-repository.md)
5. [Base](./base-repository.md)
6. [Estate](./estate-repository.md)
7. [Field](./field-repository.md)
8. [File](./file-repository.md)
9. [Filter](./filter-repository.md)
10. [Last Seen](./last-seen-repository.md)
11. [Link](./link-repository.md)
12. [Log](./log-repository.md)
13. [Macro](./macro-repository.md)
14. [Marketplace](./marketplace-repository.md)
15. [Relation](./relation-repository.md)
16. [Search Criteria](./search-criteria-repository.md)
17. [Setting](./setting-repository.md)
18. [Task](./task-repository.md)
19. [User](./user-repository.md)

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
