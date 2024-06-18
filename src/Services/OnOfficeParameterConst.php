<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Services;

trait OnOfficeParameterConst
{
    /*
     * Parameter constants for the onOffice API request.
     */
    public const DATA = 'data';

    public const LISTLIMIT = 'listlimit';

    public const LISTOFFSET = 'listoffset';

    public const FILTER = 'filter';

    public const SORTBY = 'sortby';

    public const SORTORDER = 'sortorder';

    public const RELATIONTYPE = 'relationtype';

    public const PARENTIDS = 'parentids';

    public const CHILDIDS = 'childids';

    public const RECORDIDS = 'recordids';

    public const PARAMETERCACHEID = 'parameterCacheId';

    public const EXTENDEDCLAIM = 'extendedclaim';
}
