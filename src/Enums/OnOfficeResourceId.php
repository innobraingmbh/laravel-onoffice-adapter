<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Enums;

enum OnOfficeResourceId: string
{
    case Estate = 'estate';
    case Address = 'address';
    case None = '';
}
