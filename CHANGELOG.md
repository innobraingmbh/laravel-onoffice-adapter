# Changelog
## main

## v0.5.3
- The return types of the builder methods are now static instead of self

## v0.5.2
- The request all method will now check the response for extract ability of the response

## v0.5.1
- Added missing address fake builder country iso code type setter

## v0.5.0
- Added missing address query fake method
- Added modify method to base builder

## v0.4.1
- Added missing estate files fake method

## v0.4.0
- Moved user repository query to settings repository
- Added regions to settings repository
- Added non-filterable, non-limitable, non-sortable and non-selectable traits to repositories if needed

## v0.3.0
- Added a fake implementation for the estate repository
- Added a fake implementation for the marketplace repository
- Added a fake implementation for the user repository
- Added a ResponseFactory for testing

## v0.2.3
- Added a user repository
- Added a marketplace repository
- Removed the readonly state from repositories, to allow mocking

## v0.2.2
- Added a fallback for on office config values

## v0.2.1
- Changed code style

## v0.2.0
- Added chunked method to repositories
- Added estate files builder
- Added relation repository
- Added recordIds to address builder

## v0.1.0
- Initial release
