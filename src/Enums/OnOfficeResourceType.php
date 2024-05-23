<?php

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
}
