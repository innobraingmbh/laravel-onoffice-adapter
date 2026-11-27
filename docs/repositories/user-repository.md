# User Repository

onOffice has two endpoints for users, and they are not interchangeable:

| | User records | User list |
|---|---|---|
| Entry point | `UserRepository::query()` | `UserRepository::list()` |
| Request | `read` on `user` | `get` on `users` |
| Fields | The ones you `select()` | Always the same: `id`, `username`, `firstname`, `lastname`, `email`, `meetingurl`, `appointmentColor`, `code`, `online` |
| Field names | onOffice field names (`Vorname`, `Nachname`) | English (`firstname`, `lastname`) |
| Filtering | `where()`, `orderBy()`, `count()`, `find()` | `group()`, `onlyOwn()` |
| Right | "Read out user data via API" on the API user | None documented |

Reach for the user list to offer users for selection, and for the user records to read or filter the data of the accounts themselves.

```php
use Innobrain\OnOfficeAdapter\Facades\UserRepository;
```

## User Records

```php
$users = UserRepository::query()->select(['Vorname', 'Nachname'])->get();
$user = UserRepository::query()->select(['Vorname', 'Nachname'])->first();
$user = UserRepository::query()->select(['Vorname', 'Nachname'])->find(21);
$count = UserRepository::query()->count();
```

::: warning
Always `select()` the fields you need. Without it onOffice returns no records at all: `get()` is empty, `first()` and `find()` are `null`, while `count()` still reports the users.
:::

The record carries the user id, the fields are keyed by their onOffice names:

```php
['id' => 21, 'type' => 'user', 'elements' => ['Vorname' => 'Theo', 'Nachname' => 'Test']]
```

### Filtering & Ordering

```php
$users = UserRepository::query()
    ->select(['Name', 'Vorname', 'Nachname', 'email'])
    ->whereIn('Nr', [17, 19, 21])
    ->orderBy('Nachname')
    ->get();
```

The records include every account: deactivated and locked users and API users too. Select `online` to tell them apart.

### Chunked Processing

```php
UserRepository::query()->select(['Vorname', 'Nachname'])->each(function (array $users) {
    // Process chunk
});
```

::: tip
`SettingRepository::users()` returns the same builder.
:::

## User List

```php
$users = UserRepository::list()->get();
$user = UserRepository::list()->first();
```

```php
[
    ['id' => 25, 'type' => null, 'elements' => ['id' => 25, 'username' => 'paulprobe', 'firstname' => 'Paul', 'lastname' => 'Probe', 'email' => 'paulprobe154@onoffice.de', 'meetingurl' => 'www.meetinglink3178.com', 'appointmentColor' => 'FFFF00', 'code' => 'PP', 'online' => 'online']],
    ['id' => 17, 'type' => null, 'elements' => ['id' => 17, 'username' => 'theotest', 'firstname' => 'Theo', 'lastname' => 'Test', 'email' => 'theotest154@onoffice.de', 'meetingurl' => 'www.meetinglink6542.com', 'appointmentColor' => 'B04F00', 'code' => 'TT', 'online' => 'offline']],
]
```

onOffice documents the list as "all users". Against a live tenant it held the users that can be picked in enterprise: deactivated and locked users and API users were missing, although the user records include them.

### Filtering

```php
// The users of one group
$users = UserRepository::list()->group(225)->get();

// Only the user the credentials belong to
$users = UserRepository::list()->onlyOwn()->get();
```

`group()` sends `userfilter` `byGroupId` together with the `groupId`, because onOffice ignores a `groupId` on its own. `onlyOwn()` sends `userfilter` `onlyOwn`; an API user is not part of the list, so with the credentials of an API user it returned no users. The filter set last replaces the one before.

Any other parameter of the endpoint goes through `parameter()`:

```php
$users = UserRepository::list()->parameter('userfilter', 'ownOfficeGroupOrAll')->get();
```

`get()` reads the whole list in a single request. There is no pagination, no `each()` and no `count()`, and `select()`, `where()` and `orderBy()` have no effect.
