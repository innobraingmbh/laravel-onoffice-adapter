<?php

declare(strict_types=1);

use Illuminate\Support\Collection;

if (! function_exists('clear_elements')) {
    /**
     * Clear elements from an onOffice response.
     * Input the whole response element from first(), find() or get() method.
     *
     * @param  array{id: string, type: string, elements: array} | array{array{id: string, type: string, elements: array}} | array{}  $response
     */
    function clear_elements(array $response, array $rejectValues = [[], null, '', '0.00']): array
    {
        // Determine if it's a single record or multiple records
        $isMultiple = ! isset($response['elements']);

        $cleaned = Collection::make($isMultiple ? $response : [$response])
            ->map(function (array $record) use ($rejectValues) {
                $record['elements'] = Collection::make($record['elements'])
                    ->reject(fn (mixed $value) => in_array($value, $rejectValues, true))
                    ->all();

                return $record;
            });

        if ($isMultiple) {
            return $cleaned->all();
        }

        return $cleaned->first();
    }
}
