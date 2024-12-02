# Changelog

## main
- TODO: Remove old activity repository code in next major release

## v1.7.0
- feat: add handy where helpers (whereNot, whereIn, whereNotIn, whereBetween, whereLike, whereNotLike)

## v1.6.0
- feat: add efficient count() to ActivityRepository, AddressRepository, EstateRepository, and SettingsRepository::user()

## v1.5.10
- allow passing strings to relation trait so new relations do not have to be added every time they are used
- add missing relation type to trait

## v1.5.9
- add string type to base factory id() method
 
## v1.5.8
- fix incorrect exception type on invalid create query

## v1.5.7
- enable create call in searchcriteria repository

## v1.5.6
- enable filtering in address and estate search api calls

## v1.5.5
- adds MacroRepository to resolve macros

## v1.5.4
- enable setting both estateId and addressIds on activity repository
- deprecates the current way of setting ids on activity repository

## v1.5.3
- enable search request on estate repository

## v1.5.2
- fixes user repository find function

## v1.5.1
- fixes issue where requests with strings as resource types could not be processed when generating the hmac

## v1.5.0
- feat: enable withCredentials call on Builder so config is not needed to set runtime credentials

## v1.4.4
- fix type annotation for upload function on file repository

## v1.4.3
- Enable passing strings for ResourceTypes in the OnOfficeRequestDTO so users are not constrained by the enum

## v1.4.2
- Add EstatePictures call

## v1.4.1
- Prefer response error messages over general messages when available

## v1.4.0
- Refactored the passing of the request to the on office service
- Added a method to get the last request response pair from the repository
- Added die and dump functions to the queries
- Fixed ImprintBuilder cannot return imprint with find
- Added a file to collect default fields which exist in most clients (currently only added estate info)
- Added a helper to easily clean empty fields from onOffice responses (`clean_elements`)
- Fix return type for find actions, as they might be null if find does not resolve the record

## v1.3.0
- Added a throw to stub responses

## v1.2.7
- Added a query exception to filter repository
- Added more relation types

## v1.2.6
- Added Get Filters Repository

## v1.2.5
- Fixed retry option does not handle timestamp change

## v1.2.4
- Add more address-estate relation types

## v1.2.3
- Increased default file upload block size to more performant value

## v1.2.2
- Fixed the missing overwrite of the list limit

## v1.2.1
- Added a resource type multiselectkey
- Added the body const modul

## v1.2.0
- Added a sequence method for base repository to repeat a response

## v1.1.3
- Fixes first() method on Estate- and ImprintRepository
- Adds credential validation on `HMAC Invalid` exceptions for easier debugging

## v1.1.2
- Allows integer values for elements function in factories

## v1.1.1
- Added tipster for relation type enum

## v1.1.0
- Fix on office request for field first

## v1.0.0
- Overhaul of the package to use a new fake implementation
- Overhaul of the package to remove the fake builder classes
- Added the ability to call a custom request

## v0.13.6
- Fixed typo on relation create

## v0.13.5
- Fixed estate find method

## v0.13.4
- Added a take method to limit the number of records returned

## v0.13.3
- Changed the search implementation to use the full page size

## v0.13.2
- Added a search implementation to the address repository

## v0.13.1
- Added real support of fake relation repository

## v0.13.0
- Added a exception fake implementation
- Added relation create to relation builder

## v0.12.4
- Added estate create fake method to estate repository

## v0.12.3
- Fix estate create should return array

## v0.12.2
- Added estate create to estate builder

## v0.12.1
- Added nullable faker factory

## v0.12.0
- Added nullable first method

## v0.11.0
- Fixed the upload builder fake implementation to match the real implementation

## v0.10.3
- Added previously added upload in chunks method to fake builder

## v0.10.2
- Added a upload in chunks method to the file repository

## v0.10.1
- Fixed the action for find address

## v0.10.0
- Added error code to on office exception
- Added a method to get the error from the on office exception
- Added a stringify method to an office error
- Added a handling for response errors in the status key

## v0.9.1
- Added the api claim back to the request factory

## v0.9.0
- Added direct config access for onOffice credentials

## v0.8.5
- Added action query to settings repository

## v0.8.4
- Added a fallback to empty responses on first and find in fake context

## v0.8.3
- Added search criteria factory

## v0.8.2
- Added search criteria repository

## v0.8.1
- Added a field repository fake

## v.0.8.0
- Added the parameter helper to all repositories

## v0.7.7
- Fixed typo of addressids

## v0.7.6
- Added address creation to the address repository

## v0.7.5
- Added the activity log repository

## v0.7.4
- Added imprint to the settings repository

## v0.7.3
- Added an exception when the file is not found in a estate file query
- Added a modify method for estate files

## v0.7.2
- Fixed a typo where the delete method of estate files returns an error

## v0.7.1
- Added delete to estate file repository

## v0.7.0
- Added upload file Repository

## v0.6.0
- Fixed a bug where the extended claim is not set in the request factory

## v0.5.4
- Fixed a bug where the filters are not wrapped in an array for each column

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
