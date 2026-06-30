<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponse;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeResponsePage;
use Innobrain\OnOfficeAdapter\Exceptions\OnOfficeException;
use Innobrain\OnOfficeAdapter\Exceptions\StrayRequestException;
use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;
use JsonException;
use Throwable;

class BatchBuilder extends Builder
{
    /**
     * The requests that will be sent as one batch.
     *
     * @var array<int, OnOfficeRequest>
     */
    protected array $requests = [];

    /**
     * Add one or more requests to the batch. A builder can be
     * passed directly and will be converted to its read request.
     */
    public function add(OnOfficeRequest|Builder ...$requests): static
    {
        foreach ($requests as $request) {
            $this->requests[] = $request instanceof Builder ? $request->toRequest() : $request;
        }

        return $this;
    }

    /**
     * Send all added requests in a single API call.
     * Returns one result element per request, in the same order.
     *
     * @return Collection<int, array<string, mixed>>
     *
     * @throws OnOfficeException
     * @throws Throwable
     */
    public function send(): Collection
    {
        throw_if($this->requests === [], OnOfficeException::class, 'Cannot send an empty batch');

        $requests = array_map(fn (OnOfficeRequest $request): OnOfficeRequest => $this->runBeforeSendingCallbacks($request), $this->requests);

        $response = $this->getBatchStubResponse();

        if (is_null($response)) {
            throw_if($this->preventStrayRequests, StrayRequestException::class, request: $requests[0]);

            $response = $this->getOnOfficeService()->requestApiBatch($requests);
        } else {
            $this->getOnOfficeService()->throwIfBatchResponseIsFailed($response);
        }

        foreach ($requests as $index => $request) {
            $this->repository->recordRequestResponsePair($request, $this->extractActionResponse($response, $index));
        }

        $response = $this->runAfterSendingCallbacks($response);

        /** @var array<int, array<string, mixed>> $results */
        $results = $response->json('response.results', []);

        return collect($results);
    }

    /**
     * Build a single stub response from the next faked response.
     * Each page of the faked response becomes one result of the batch.
     *
     * @throws JsonException
     */
    protected function getBatchStubResponse(): ?Response
    {
        /** @var ?OnOfficeResponse $stub */
        $stub = ($this->stubCallables ?? collect())->shift();

        if (is_null($stub)) {
            return null;
        }

        $pages = [];
        while (! $stub->isEmpty()) {
            $pages[] = $stub->shift();
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
    protected function extractActionResponse(Response $response, int $index): array
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
