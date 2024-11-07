<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Enums;

enum OnOfficeResourceType: string
{
    case Address = 'address';
    case Estate = 'estate';
    case EstatePictures = 'estatepictures';
    case Fields = 'fields';
    case Filters = 'filters';
    case File = 'file';
    case IdsFromRelation = 'idsfromrelation';
    case User = 'user';
    case Users = 'users';
    case UserPhoto = 'userphoto';
    case UnlockProvider = 'unlockProvider';
    case Regions = 'regions';
    case UploadFile = 'uploadfile';
    case FileRelation = 'fileRelation';
    case Impressum = 'impressum';
    case Activity = 'agentslog';
    case SearchCriteria = 'searchcriterias';
    case ActionTypes = 'actionkindtypes';
    case Relation = 'relation';
    case Search = 'search';
    case MultiselectKey = 'multiselectkey';
    case MacroResolve = 'macroresolve';
}
