# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
# Run tests
composer test

# Run a single test file
vendor/bin/pest tests/Repositories/EstateRepositoryTest.php

# Run a single test by name
vendor/bin/pest --filter "test name"

# Static analysis (PHPStan level 8)
composer analyse

# Code formatting (Laravel Pint)
composer format
```

## Architecture

This is a Laravel package that provides a fluent query builder interface for the onOffice real estate API.

### Core Layers

**Facades** (`src/Facades/`) - Entry points that resolve to Repository singletons. Used as `EstateRepository::query()`.

**Repositories** (`src/Repositories/`) - Extend `BaseRepository`. Each repository creates its own Builder type and manages request faking/recording for tests.

**Builders** (`src/Query/`) - Extend `Builder`. Implement the fluent API (`select()`, `where()`, `get()`, `find()`, etc.) and construct `OnOfficeRequest` objects. Each entity type (Estate, Address, etc.) has its own builder with entity-specific methods.

**OnOfficeService** (`src/Services/OnOfficeService.php`) - Handles HTTP requests to the API, HMAC authentication, pagination, retries, and error handling.

### Request Flow

1. `EstateRepository::query()` creates an `EstateBuilder`
2. Builder methods accumulate query state (`columns`, `filters`, `orderBy`, etc.)
3. Terminal methods (`get()`, `find()`, `first()`) create `OnOfficeRequest` and call `requestApi()` or `requestAll()`
4. `OnOfficeService` handles the actual HTTP call with HMAC signing and retry logic

### Testing Pattern

Repositories have a built-in fake system for testing:

```php
EstateRepository::fake(EstateRepository::response([
    EstateRepository::page(recordFactories: [
        EstateFactory::make()->id(1),
    ]),
]));

$response = EstateRepository::query()->get();

EstateRepository::assertSentCount(1);
```

Factory classes in `src/Facades/Testing/RecordFactories/` build fake response data.

### Live API Probes

For exploring or debugging endpoints against the real API, use `bootstrap/probe.php`. It boots a Testbench Laravel container, loads `ON_OFFICE_TOKEN` / `ON_OFFICE_SECRET` from the package-root `.env`, registers the service provider, and returns a booted app. Facades work normally afterwards.

Drop one-off scripts into `scratch/` (gitignored):

```php
<?php
require __DIR__.'/../bootstrap/probe.php';

use Innobrain\OnOfficeAdapter\Facades\EstateRepository;

dump(EstateRepository::query()->select(['Id', 'kaufpreis'])->first());
```

Run with `php scratch/whatever.php`. The bootstrap exits with a clear error if credentials are missing — it never falls through silently.

This is for development feedback only, not test infrastructure. Do not commit probes; do not call write endpoints (`create` / `modify` / `delete`) without confirming the target tenant.

### Key Traits

- `Concerns/Input` - Adds `input()` method for search queries
- `Concerns/RecordIds` - Adds `recordIds()` for bulk operations
- `Concerns/NonFilterable`, `NonOrderable`, `NonSelectable` - Disable builder features for specific endpoints

## onOffice API Documentation

Local mirror of the onOffice API docs is available for offline reference. See `onoffice-docs/apidoc.md` for regeneration instructions.

```bash
# Search API docs
rg "search term" onoffice-docs/apidoc-text
```

**Covered modules:** Estates, Addresses, Search Criteria, Activities, Files/Templates (partial), Marketplace, Settings (partial)

**Missing modules:** Appointments, Tasks, Relations, Emails, Miscellaneous (macros, logs, surveys, timetracking, links)
