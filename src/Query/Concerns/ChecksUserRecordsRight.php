<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query\Concerns;

use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeApiCredentials;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Facades\BaseRepository as BaseRepositoryFacade;
use Innobrain\OnOfficeAdapter\Query\Builder;
use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;
use JsonException;

trait ChecksUserRecordsRight
{
    /**
     * Filter the response so it only keeps the records the user may access.
     *
     * Checks each record in the response and removes every one the user has no
     * right to, without changing anything else in the response (e.g.
     * count_absolute).
     *
     * @param  string  $action  The action to check rights for (e.g. 'get', 'edit').
     * @param  string  $module  The module name to check rights in (e.g. 'estate', 'address').
     * @param  int  $userId  The ID of the user whose rights are being checked.
     * @param  string  $resultPath  The dot-notated path to the records in the response body.
     *                              Defaults to OnOfficeResponsePath::RECORDS.
     * @return self Returns the current Builder instance for method chaining.
     */
    public function checkUserRecordsRight(string $action, string $module, int $userId, string $resultPath = OnOfficeResponsePath::RECORDS): self
    {
        return $this->after([
            function (Response $response, string $action, string $module, int $userId) use ($resultPath): ?Response {
                if ($response->failed()) {
                    return null;
                }

                /** @var array<int, int|string> $ids */
                $ids = $response->json(OnOfficeResponsePath::RECORD_IDS, []);

                if ($ids === []) {
                    return $this->replaceRecords($response, $resultPath, []);
                }

                $allowedIds = $this->allowedRecordIds($action, $module, $userId, $ids);

                /** @var array<int, array{id: int|string}> $records */
                $records = $response->json($resultPath, []);

                $records = collect($records)
                    ->filter(fn (array $record): bool => $allowedIds->contains((int) $record['id']))
                    ->all();

                return $this->replaceRecords($response, $resultPath, $records);
            },
            $action,
            $module,
            $userId,
        ]);
    }

    /**
     * Ask the API which of the given record ids the user is allowed to access.
     *
     * @param  array<int, int|string>  $recordIds
     * @return Collection<int, int>
     */
    private function allowedRecordIds(string $action, string $module, int $userId, array $recordIds): Collection
    {
        $response = BaseRepositoryFacade::query()
            ->when($this->credentials, fn (Builder $query, OnOfficeApiCredentials $credentials) => $query->withCredentials($credentials))
            ->requestApi(new OnOfficeRequest(
                actionId: OnOfficeAction::Get,
                resourceType: OnOfficeResourceType::CheckUserRecordsRight,
                parameters: [
                    'action' => $action,
                    'module' => $module,
                    'userid' => $userId,
                    'recordIds' => $recordIds,
                ],
            ));

        /** @var array<int, int|string> $allowedIds */
        $allowedIds = $response->json(OnOfficeResponsePath::FIRST_RECORD_ELEMENTS, []);

        return collect($allowedIds)->map(fn (int|string $element): int => (int) $element);
    }

    /**
     * Rebuild the response with the records at $resultPath replaced, leaving
     * status, headers and every other body key (e.g. count_absolute) untouched.
     *
     * @param  array<int, array<string, mixed>>  $records
     *
     * @throws JsonException
     */
    private function replaceRecords(Response $response, string $resultPath, array $records): Response
    {
        $responseBody = $response->json();
        data_set($responseBody, $resultPath, array_values($records));
        $psrResponse = $response->toPsrResponse();

        return new Response(new Psr7Response(
            $psrResponse->getStatusCode(),
            $psrResponse->getHeaders(),
            json_encode($responseBody, JSON_THROW_ON_ERROR),
            $psrResponse->getProtocolVersion(),
            $psrResponse->getReasonPhrase(),
        ));
    }
}
