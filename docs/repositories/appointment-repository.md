# Appointment Repository

Manage calendar appointments in onOffice. Uses the `appointmentList` resource for listing and the `calendar` resource for CRUD operations.

## Listing Appointments

Both `dateRange()` and `select()` are required for listing appointments — the API rejects requests without fields.

::: warning
The API ignores all pagination parameters for appointment listings and caps a window at 500 records — query smaller date ranges when a window can exceed that.
:::

```php
use Innobrain\OnOfficeAdapter\Facades\AppointmentRepository;

$appointments = AppointmentRepository::query()
    ->dateRange('2025-01-01', '2025-12-31')
    ->select(['subject', 'type', 'status', 'date'])
    ->get();

$appointment = AppointmentRepository::query()
    ->dateRange('2025-01-01', '2025-01-31')
    ->select(['subject', 'type', 'status', 'date'])
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

Two more read quirks: recurring appointments are returned anchored on the series start date — even when that date lies outside the queried window — so in-window occurrences must be computed from the `recurrence` element. And dates come back as timezone-qualified UTC (`2026-08-03T06:00:00+00:00`) while writes take local wall time (`'start_dt' => '2026-08-03 08:00:00'`).

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

`cancelled()`, `done()` and `recurrent()` filter strictly by their boolean — to include both states, don't call the method at all.

::: warning
Appointments cannot be filtered by linked estate or contact: the API silently ignores every such filter key and returns the full window. Resolve linked appointments via the [Relation Repository](./relation-repository.md) (`CalendarEstate` / `CalendarAddress`, with the estate or address ID as `childIds()`), then fetch them by ID.
:::

Standard `where()` filters work for `notes`, `subject`, `type`, and `createdBy`:

```php
$appointments = AppointmentRepository::query()
    ->dateRange('2025-01-01', '2025-12-31')
    ->where('subject', 'like', '%Besichtigung%')
    ->where('notes', '!=', 'internal')
    ->get();
```

## Finding a Single Appointment

Uses the `appointmentList` Get endpoint with a resource id:

```php
$appointment = AppointmentRepository::query()->find(42);
```

A nonexistent ID is not an error — the API answers with zero records, so `find()` returns `null`.

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

The response contains only the new appointment's ID — no fields are echoed back.

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

::: warning
`relatedAddressIds` on modify is additive — existing links are kept. Replace them by also passing `->parameter('replaceAddressIds', true)`; unlink the estate with the literal `->parameter('relatedEstateId', 0)`.
:::

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

`transitTimePre` and `transitTimePost` are mandatory even with `'allowTransitTime' => false` — omitting them fails with error 305, which masks any other problem in the payload.

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

The call returns success unconditionally — even when the appointment has no linked contact, the contact has no email address, or the appointment does not exist. No delivery status is available.

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

::: danger
When making an existing appointment recurring via `modify`, resend `start_dt` — without it the series is anchored on `0000-00-00` and the appointment disappears from every date-window listing while staying readable by ID.
:::

Open-ended series (`'rp_ende_status' => 2`, no `rp_ende_datum`) are stored and read back correctly but never rendered in the onOffice calendar UI — send a far-future `rp_ende_datum` instead.
