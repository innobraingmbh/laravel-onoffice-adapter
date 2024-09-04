<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Enums;

enum OnOfficeResourceType: string
{
    case Address = 'address';
    case Estate = 'estate';
    case Fields = 'fields';
    case File = 'file';
    case IdsFromRelation = 'idsfromrelation';
    case User = 'user';
    case Users = 'users';
    case UnlockProvider = 'unlockProvider';
    case Regions = 'regions';
    case UploadFile = 'uploadfile';
    case FileRelation = 'fileRelation';
    case Impressum = 'impressum';
    case Activity = 'agentslog';
    case SearchCriteria = 'searchcriterias';
    case ActionTypes = 'actionkindtypes';
    case Relation = 'relation';
}
