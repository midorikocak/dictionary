<?php

declare(strict_types=1);

namespace midorikocak\dictionary;

class Entry implements ArrayableInterface
{
    private ?int $id;
    private ?int $titleId;
    private string $content;

    public function __construct(string $content, ?int $id = null, ?int $titleId = null)
    {
        $this->content = $content;
        $this->id = $id;
        $this->titleId = $titleId;
    }

    public function toArray(): array
    {
        $toReturn = [
            'content' => $this->content,
        ];

        if ($this->id) {
            $toReturn['id'] = $this->id;
        }
        if ($this->titleId) {
            $toReturn['title_id'] = $this->titleId;
        }
        return $toReturn;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id)
    {
        $this->id = $id;
    }

    public function getTitleId(): ?int
    {
        return $this->titleId;
    }

    public function setTitleId(?int $titleId)
    {
        $this->titleId = $titleId;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }
}
