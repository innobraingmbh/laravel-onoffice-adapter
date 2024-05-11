<?php

namespace Katalam\OnOfficeAdapter\Enums;

enum OnOfficeAction: string
{
    case Read = 'urn:onoffice-de-ns:smart:2.5:smartml:action:read';
    case Create = 'urn:onoffice-de-ns:smart:2.5:smartml:action:create';
    case Modify = 'urn:onoffice-de-ns:smart:2.5:smartml:action:modify';
    case Get = 'urn:onoffice-de-ns:smart:2.5:smartml:action:get';
    case Do = 'urn:onoffice-de-ns:smart:2.5:smartml:action:do';
    case Delete = 'urn:onoffice-de-ns:smart:2.5:smartml:action:delete';
}
