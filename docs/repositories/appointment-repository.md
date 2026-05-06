# Appointment Repository

Manage calendar appointments in onOffice. Uses the `appointmentList` resource for listing and the `calendar` resource for CRUD operations.

## Listing Appointments

The `dateRange()` method is required for listing appointments.

```php
use Innobrain\OnOfficeAdapter\Facades\AppointmentRepository;

$appointments = AppointmentRepository::query()
    ->dateRange('2025-01-01', '2025-12-31')
    ->get();

$appointment = AppointmentRepository::query()
    ->dateRange('2025-01-01', '2025-01-31')
    ->first();
```

## Selecting Fields

The `appointmentList` endpoint has its own field names, different from the `calendar` fields used for create/modify.

```php
$appointments = AppointmentRepository::query()
    ->dateRange('2025-01-01', '2025-12-31')
    ->select([
        'id', 'subject', 'notes', 'type', 'status',
        'date', 'location', 'users', 'groups',
        'contacts', 'estate', 'project',
        'confirmationStatus', 'private',
        'travelTime', 'recurrence', 'reminder',
        'resources', 'conflicts',
    ])
    ->get();
```

::: tip
The `appointmentList` returns structured objects for fields like `type`, `status`, `date`, and `location` instead of flat values.
:::

## Filtering

Filter by users, groups, and appointment state:

```php
$appointments = AppointmentRepository::query()
    ->dateRange('2025-01-01', '2025-12-31')
    ->users([21, 23])
    ->groups([168])
    ->cancelled(false)
    ->done(false)
    ->recurrent(true)
    ->get();
```

Standard `where()` filters work for `notes`, `subject`, `type`, and `createdBy`:

```php
$appointments = AppointmentRepository::query()
    ->dateRange('2025-01-01', '2025-12-31')
    ->where('subject', 'like', '%Besichtigung%')
    ->where('notes', '!=', 'internal')
    ->get();
```

## Finding a Single Appointment

Uses the deprecated `calendar` Read endpoint (the only way to fetch by ID):

```php
$appointment = AppointmentRepository::query()->find(42);
```

## Creating Appointments

Fields go inside a `data` key. `start_dt` and `end_dt` are mandatory.

```php
$appointment = AppointmentRepository::query()->create([
    'data' => [
        'description' => 'Property Viewing',
        'start_dt' => '2025-06-15 14:00:00',
        'end_dt' => '2025-06-15 15:00:00',
        'art' => 'Besichtigung',
        'note' => 'Meet at the front door',
        'ganztags' => false,
        'private' => false,
        'erinnerung' => '30 minutes',
        'ressources' => ['Firmenfahrzeug'],
    ],
    'relatedAddressIds' => [1935, 1931],
    'relatedEstateId' => 608,
    'location' => ['estate' => 608],
    'subscribers' => [
        'users' => [14],
        'groups' => [168, 172],
    ],
    'reminderTypes' => ['email', 'popup'],
]);
```

### Create Data Fields

| Field | Type | Description |
|-------|------|-------------|
| `description` | STRING | Appointment title/description |
| `start_dt` | STRING | Start datetime (mandatory) |
| `end_dt` | STRING | End datetime (mandatory) |
| `art` | STRING | Appointment type |
| `note` | STRING | Notes |
| `ganztags` | BOOL | All-day appointment |
| `private` | BOOL | Private appointment |
| `status` | STRING | `active`, `completed`, `canceled`, `participantsAvailable` |
| `erinnerung` | STRING | Reminder time (e.g. `30 minutes`, `1 hours`, `2 days`) |
| `von` | STRING | Creator username |
| `ressources` | ARRAY | Room/equipment names |
| `allowTransitTime` | BOOL | Enable transit time |
| `transitTimePre` | STRING | Transit time before (e.g. `00:30:00`) |
| `transitTimePost` | STRING | Transit time after |

### Location Options

```php
['estate' => 608]              // Estate address
['address' => '5431']          // Contact address
['user' => '21']               // User's address
['group' => '39']              // Group address
['mandant' => true]            // Company address
['sonstiges' => 'Custom addr'] // Free text
['customVideoUrl' => 'https://meet.example.com']
['userMeetingUrl' => 17]       // User's meeting link
```

## Modifying Appointments

```php
AppointmentRepository::query()
    ->addModify('note', 'Updated notes')
    ->addModify('description', 'New title')
    ->modify(42);

// With related data
AppointmentRepository::query()
    ->addModify('status', 'completed')
    ->parameter('subscribers', ['users' => [14, 21]])
    ->parameter('location', ['estate' => 608])
    ->modify(42);
```

## Deleting Appointments

```php
AppointmentRepository::query()->delete(42);
```

## Chunked Processing

```php
AppointmentRepository::query()
    ->dateRange('2025-01-01', '2025-12-31')
    ->each(function (array $appointments) {
        // Process chunk
    });
```

## Appointment Files

```php
$files = AppointmentRepository::files(42)->get();
```

## Appointment Conflicts

Check for scheduling conflicts before creating an appointment:

```php
$conflicts = AppointmentRepository::query()->conflicts([
    'data' => [
        'start_dt' => '2025-06-15 14:00:00',
        'end_dt' => '2025-06-15 15:00:00',
        'ganztags' => false,
        'allowTransitTime' => true,
        'transitTimePre' => '00:00:00',
        'transitTimePost' => '00:00:00',
        'ressources' => ['Konferenzraum'],
        'status' => 'active',
    ],
    'subscribers' => [
        'users' => [14, 21],
        'groups' => [],
    ],
]);
```

Returns arrays of `conflictedUsers`, `conflictedResources`, `conflictedAddresses`, and `conflictedEstates`.

## Calendar Resources

Query available rooms, vehicles, and other bookable resources:

```php
$resources = AppointmentRepository::query()->resources();
```

## Send Appointment Confirmation

Trigger confirmation emails to appointment participants:

```php
$result = AppointmentRepository::query()->sendConfirmation(
    calendarId: 42,
    useDefaultMailAccount: false,
);
```

## Recurring Appointments

Set recurrence via the create/modify `data` fields:

| Field | Description |
|-------|-------------|
| `rp_flag` | `true` to enable recurrence |
| `rp_type` | `t` (daily), `w` (weekly), `m` (monthly), `j` (yearly) |
| `rp_tage` | Interval (1-999) |
| `rp_beginn_datum` | Start date (`YYYY-MM-DD`) |
| `rp_ende_datum` | End date (`YYYY-MM-DD`) |
| `rp_ende_status` | `1` = has end date, `2` = open-ended |
| `rp_exception` | Exception dates: `#2025-06-20#2025-06-27` |

```php
AppointmentRepository::query()->create([
    'data' => [
        'description' => 'Weekly Team Meeting',
        'start_dt' => '2025-06-02 09:00:00',
        'end_dt' => '2025-06-02 10:00:00',
        'rp_flag' => true,
        'rp_type' => 'w',
        'rp_tage' => 1,
        'rp_beginn_datum' => '2025-06-02',
        'rp_ende_datum' => '2025-12-31',
        'rp_ende_status' => 1,
    ],
    'subscribers' => ['users' => [14, 21]],
]);
```
