<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Query;

use Illuminate\Support\Arr;
use Innobrain\OnOfficeAdapter\Dtos\OnOfficeRequest;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeAction;
use Innobrain\OnOfficeAdapter\Enums\OnOfficeResourceType;
use Innobrain\OnOfficeAdapter\Services\OnOfficeResponsePath;
use Innobrain\OnOfficeAdapter\Services\OnOfficeService;

class MacroBuilder extends Builder
{
    public function resolve(): string
    {
        $request = new OnOfficeRequest(
            OnOfficeAction::Get,
            OnOfficeResourceType::MacroResolve,
            parameters: [
                ...$this->customParameters,
            ]
        );

        return $this->requestApi($request)
            ->json(OnOfficeResponsePath::FIRST_RECORD_ELEMENTS_RESOLVED_TEXT);
    }

    /**
     * Set the text to resolve
     */
    public function text(string $text): static
    {
        return $this->parameter('text', $text);
    }

    /**
     * Set if the text is HTML
     */
    public function isHtml(bool $isHtml = true): static
    {
        return $this->parameter('ishtml', $isHtml);
    }

    /**
     * Set estate IDs for context
     *
     * @param  int|array<int, int>  $ids
     */
    public function estateIds(int|array $ids): static
    {
        return $this->parameter(OnOfficeService::ESTATEIDS, Arr::wrap($ids));
    }

    /**
     * Set address IDs for context
     *
     * @param  int|array<int, int>  $ids
     */
    public function addressIds(int|array $ids): static
    {
        return $this->parameter(OnOfficeService::ADDRESSIDS, Arr::wrap($ids));
    }

    /**
     * Set appointment IDs for context
     *
     * @param  int|array<int, int>  $ids
     */
    public function appointmentIds(int|array $ids): static
    {
        return $this->parameter('appointmentids', Arr::wrap($ids));
    }

    /**
     * Set agent log IDs for context
     *
     * @param  int|array<int, int>  $ids
     */
    public function agentLogIds(int|array $ids): static
    {
        return $this->parameter('agentlogids', Arr::wrap($ids));
    }
}
