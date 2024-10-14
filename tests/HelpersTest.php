<?php

declare(strict_types=1);

test('clear_elements removes empty elements from a single record', function () {
    $input = [
        'id' => '1',
        'type' => 'estate',
        'elements' => [
            'name' => 'Test Estate',
            'price' => '100000',
            'description' => '',
            'rooms' => '0.00',
            'area' => null,
            'features' => [],
        ],
    ];

    $expected = [
        'id' => '1',
        'type' => 'estate',
        'elements' => [
            'name' => 'Test Estate',
            'price' => '100000',
        ],
    ];

    $result = clear_elements($input);

    expect($result)->toEqual($expected);
});

test('clear_elements removes empty elements from multiple records', function () {
    $input = [
        [
            'id' => '1',
            'type' => 'estate',
            'elements' => [
                'name' => 'Estate 1',
                'price' => '100000',
                'description' => '',
            ],
        ],
        [
            'id' => '2',
            'type' => 'estate',
            'elements' => [
                'name' => 'Estate 2',
                'price' => '0.00',
                'area' => null,
            ],
        ],
    ];

    $expected = [
        [
            'id' => '1',
            'type' => 'estate',
            'elements' => [
                'name' => 'Estate 1',
                'price' => '100000',
            ],
        ],
        [
            'id' => '2',
            'type' => 'estate',
            'elements' => [
                'name' => 'Estate 2',
            ],
        ],
    ];

    $result = clear_elements($input);

    expect($result)->toEqual($expected);
});

test('clear_elements handles an empty input array', function () {
    $input = [];
    $result = clear_elements($input);
    expect($result)->toEqual([]);
});

test('clear_elements preserves non-empty values', function () {
    $input = [
        'id' => '1',
        'type' => 'estate',
        'elements' => [
            'name' => 'Test Estate',
            'price' => '100000',
            'rooms' => '3',
            'area' => '150.5',
            'features' => ['garden', 'garage'],
        ],
    ];

    $expected = [
        'id' => '1',
        'type' => 'estate',
        'elements' => [
            'name' => 'Test Estate',
            'price' => '100000',
            'rooms' => '3',
            'area' => '150.5',
            'features' => ['garden', 'garage'],
        ],
    ];

    $result = clear_elements($input);

    expect($result)->toEqual($expected);
});
