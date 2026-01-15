<?php

declare(strict_types=1);

if (! function_exists('clear_elements')) {
    /**
     * Clear elements from an onOffice response.
     * Input the whole response element from first(), find() or get() method.
     *
     * @param  array{id: string, type: string, elements: array<string, mixed>}|array<int, array{id: string, type: string, elements: array<string, mixed>}>|array{}  $response
     * @param  array<int, mixed>  $rejectValues
     * @return array{id: string, type: string, elements: array<string, mixed>}|array<int, array{id: string, type: string, elements: array<string, mixed>}>
     */
    function clear_elements(array $response, array $rejectValues = [[], null, '', '0.00']): array
    {
        // Determine if it's a single record or multiple records
        $isMultiple = ! isset($response['elements']);

        /** @var array<int, array{id: string, type: string, elements: array<string, mixed>}> $records */
        $records = $isMultiple ? $response : [$response];

        $cleaned = collect($records)
            ->map(function (array $record) use ($rejectValues) {
                /** @var array<string, mixed> $elements */
                $elements = $record['elements'];

                $record['elements'] = collect($elements)
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
