<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Enums;

enum OnOfficeRelationType: string
{
    case Buyer = 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:buyer';
    case Tenant = 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:renter';
    case Owner = 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:owner';
    case ProspectiveBuyer = 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:interested';
    case ContactPersonBroker = 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:contactPerson';
    case ContactPersonAll = 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:contactPersonAll';
}
