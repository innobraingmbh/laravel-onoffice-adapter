<?php

declare(strict_types=1);

namespace Innobrain\OnOfficeAdapter\Facades\Testing\RecordFactories;

class EstateLanguageFactory extends BaseFactory
{
    public string $type = 'estateLanguage';

    public array $elements = [
        'language' => 'DEU',
        'isMain' => true,
        'mainLangId' => 1,
    ];

    public function language(string $language): static
    {
        $this->elements['language'] = $language;

        return $this;
    }

    public function isMain(bool $isMain): static
    {
        $this->elements['isMain'] = $isMain;

        return $this;
    }

    public function mainLangId(int $mainLangId): static
    {
        $this->elements['mainLangId'] = $mainLangId;

        return $this;
    }
}
