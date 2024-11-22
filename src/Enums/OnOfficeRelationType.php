<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Enums;

enum OnOfficeRelationType: string
{
    case Buyer = 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:buyer';
    case Tenant = 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:renter';
    case Owner = 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:owner';
    case Tipster = 'urn:onoffice-de-ns:smart:2.5:relationTypes:address:estate:tipp';
    case ProspectiveBuyer = 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:interested';
    case ContactPersonBroker = 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:contactPerson';
    case ContactPersonAll = 'urn:onoffice-de-ns:smart:2.5:relationTypes:estate:address:contactPersonAll';
    case EstateOfferAngebot = 'urn:onoffice-de-ns:smart:2.5:relationTypes:address:estate:offer';
    case EstateContacted = 'urn:onoffice-de-ns:smart:2.5:relationTypes:address:estate:contacted';
    case EstateMatching = 'urn:onoffice-de-ns:smart:2.5:relationTypes:address:estate:matching';
    case EstateOffer = 'urn:onoffice-de-ns:smart:2.5:relationTypes:address:estate:offerByAgentsLog';
    case ComplexEstateUnits = 'urn:onoffice-de-ns:smart:2.5:relationTypes:complex:estate:units';
    case AddressHierarchy = 'urn:onoffice-de-ns:smart:2.5:relationTypes:address:contact:address';
}
