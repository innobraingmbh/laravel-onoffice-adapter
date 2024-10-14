<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Services;

trait OnOfficeDefaultFieldConst
{
    // Contains default fields for resources in onOffice API.
    // These fields are helpful to quickly take a look at data.
    // Note that some clients may have deactivated some fields,
    // which will lead to a failed request.
    // You will need to try to remove those fields from
    // the request to get a successful response.

    const DEFAULT_ESTATE_INFO_FIELDS = [
        'anzahl_zimmer',
        'objektart',
        'objekttyp',
        'property_type',
        'regionaler_zusatz',
        'aussicht',
        'etage',
        'barrierefrei',
        'rollstuhlgerecht',
        'seniorengerecht',
        'fahrstuhl',
        'wohnflaeche',
        'vermarktungsart',
        'zustand',
        'anzahl_schlafzimmer',
        'anzahl_badezimmer',
        'unterkellert',
        'unterkellertText',
        'baujahr',
        'bauweise',
        'jahrLetzteModernisierung',
        'letzteModernisierung',
        'energiestandard',
        'ausstattungsqualitaet',
        'boden',
        'bad',
        'gaesteWc',
        'kueche',
        'klimatisiert',
        'kamin',
        'gartennutzung',
        'terrasse',
        'ausricht_balkon_terrasse',
        'balkon',
        'swimmingpool',
        'sauna',
        'haustiere',
        'hausgeld',
        'vermietet',
        'mieteinnahmen_pro_jahr_ist',
        'nebenkosten',
        'verfuegbar_ab',
        'visit_remark',
    ];
}
