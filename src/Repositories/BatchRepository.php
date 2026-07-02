<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Repositories;

use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeApiCredentials;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponse;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponsePage;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Exceptions\StrayRequestException;
use Innobrain\OnOfficeAdapter\Query\Builder;
use Innobrain\OnOfficeAdapter\Query\PendingBatch;
use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;
use JsonException;
use Throwable;

class BatchRepository extends BaseRepository
{
    /**
     * Start a batch of requests that will be sent in a single API call.
     *
     * @param  array<int, OnOfficeRequest|Builder>  $requests
     */
    public function batch(array $requests = []): PendingBatch
    {
        return new PendingBatch($this, $requests);
    }

    /**
     * Send all requests in a single API call and return one result per request,
     * in the same order. Each result is a single, non-paginated page: the first
     * page only, capped at 500 records per action.
     *
     * @param  array<int, OnOfficeRequest>  $requests
     * @return Collection<int, array<string, mixed>>
     *
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function dispatch(array $requests, ?OnOfficeApiCredentials $credentials = null, bool $preventStrayRequests = false): Collection
    {
        throw_if($requests === [], OnOfficeException::class, 'Cannot send an empty batch');

        $service = $this->onOfficeService($credentials);

        $response = $this->stubResponse();

        if (is_null($response)) {
            throw_if($this->preventStrayRequests || $preventStrayRequests, StrayRequestException::class, request: $requests[0]);

            $response = $service->requestApiBatch($requests);
        } else {
            $service->throwIfBatchResponseIsFailed($response, count($requests));
        }

        if ($this->recording) {
            foreach ($requests as $index => $request) {
                $this->recordRequestResponsePair($request, $this->actionResponse($response, $index));
            }
        }

        /** @var array<int, array<string, mixed>> $results */
        $results = $response->json('response.results', []);

        return collect($results);
    }

    protected function onOfficeService(?OnOfficeApiCredentials $credentials): OnOfficeService
    {
        return resolve(OnOfficeService::class)->setCredentials($credentials);
    }

    /**
     * Build a single stub response from the next faked response.
     * Each page of the faked response becomes one result of the batch.
     *
     * @throws JsonException
     * @throws Throwable
     */
    protected function stubResponse(): ?Response
    {
        /** @var ?OnOfficeResponse $stub */
        $stub = $this->stubCallables->shift();

        if (is_null($stub)) {
            return null;
        }

        $pages = [];
        while (! $stub->isEmpty()) {
            $pages[] = $stub->shift();
        }

        foreach (array_slice($pages, 1) as $page) {
            $status = $page->toStatusArray();

            throw_if(
                $status['code'] >= 300 || $status['errorcode'] > 0,
                OnOfficeException::class,
                'A batch response has a single top-level status, taken from the first faked page. Fail a single action with errorCodeResult/messageResult instead.',
            );
        }

        $body = [
            'status' => $pages === [] ? ['code' => 200, 'errorcode' => 0, 'message' => 'OK'] : $pages[0]->toStatusArray(),
            'response' => [
                'results' => array_map(static fn (OnOfficeResponsePage $page): array => $page->toResultArray(), $pages),
            ],
        ];

        return new Response(new Psr7Response(200, [], json_encode($body, JSON_THROW_ON_ERROR)));
    }

    /**
     * Extract the response for a single action from the batch response,
     * so each recorded request is paired with its own result.
     *
     * @return array<string, mixed>
     */
    protected function actionResponse(Response $response, int $index): array
    {
        return [
            'status' => $response->json('status'),
            'response' => [
                'results' => [
                    $response->json(OnOfficeResponsePath::result($index)),
                ],
            ],
        ];
    }
}
