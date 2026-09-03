# Repositories

Each repository corresponds to one onOffice resource.

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
15. [Query](./query-repository.md)
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

