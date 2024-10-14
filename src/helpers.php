<?php

declare(strict_types=1);

use Illuminate\Support\Collection;

if (! function_exists('clear_elements')) {
    /**
     * Clear elements from an onOffice response
     *
     * @param  array{id: string, type: string, elements: array} | array{array{id: string, type: string, elements: array}} | array{}  $response
     */
    function clear_elements(array $response): array
    {
        // Determine if it's a single record or multiple records
        $isMultiple = ! isset($response['elements']);

        $cleaned = Collection::make($isMultiple ? $response : [$response])
            ->map(function (array $record) {
                $record['elements'] = Collection::make($record['elements'])
                    ->reject(fn ($value) => match ($value) {
                        [], null, '', '0.00' => true,
                        default => false,
                    })
                    ->all();

                return $record;
            });

        if ($isMultiple) {
            return $cleaned->all();
        }

        return $cleaned->first();
    }
}
