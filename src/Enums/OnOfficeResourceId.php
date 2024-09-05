<?php

declare(strict_types=1);

namespace Katalam\OnOfficeAdapter\Enums;

enum OnOfficeResourceId: string
{
    case Estate = 'estate';
    case Address = 'address';
    case None = '';
}
