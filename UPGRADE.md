# Upgrade Guide

## From v1 to v2

### Paginated reads throw on any failing page

`get()`, `each()`, and `chunked()` used to catch an `OnOfficeException` on any page after the first, log it, and return the pages collected so far — as if the read had succeeded.

They now let the exception propagate, from any page, including the first.

If you relied on a partial result being returned silently, wrap the call in a `try`/`catch`:

```php
try {
    $estates = EstateRepository::query()->where('status', 1)->get();
} catch (OnOfficeException $e) {
    // handle the failed page yourself
}
```

Faked repositories already threw on every page, so tests are unaffected. Note that chunks already handed to an `each()` callback are not rolled back when a later page fails.

### Deprecated `ActivityBuilder` methods removed

`estate()`, `address()`, `recordIdsAsEstate()`, `recordIdsAsAddress()`, and `recordIds()` are removed from `ActivityBuilder`. They were deprecated back in v1.5.4.

Replace them with `estateId()` and `addressIds()`:

```php
// Before
ActivityRepository::query()->address()->recordIds([34, 35])->get();
ActivityRepository::query()->recordIdsAsAddress()->recordIds([34, 35])->get();

// After
ActivityRepository::query()->addressIds([34, 35])->get();
```

`estate()`/`recordIdsAsEstate()` never actually filtered by estate — combined with `estateOrAddress === 'estate'` being the default, `prepareEstateOrAddressParameters()` discarded `recordIds()` in that mode, so the request carried no estate filter at all. Use `estateId($id)`, which was already the correct way to filter by a single estate:

```php
ActivityRepository::query()->estateId(41)->get();
```

### Sort direction fix on Address/Activity reads and search

`orderBy()` on `AddressBuilder`/`ActivityBuilder` reads, and on `EstateBuilder`/`AddressBuilder` `search()`, previously encoded the sort direction incorrectly and silently ignored it. This is a bug fix, not a breaking change — but if your tests pinned the old (broken) request payloads, they'll need updating to expect a correct `sortby` map.
