<?php

declare(strict_types=1);

namespace midorikocak\dictionary;

use function htmlspecialchars;

class Example implements ArrayableInterface
{
    private ?int $id;
    private ?int $entryId;
    private string $content;

    public function __construct(string $content, ?int $id = null, ?int $entryId = null)
    {
        $this->content = $content;
        $this->id = $id;
        $this->entryId = $entryId;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id)
    {
        $this->id = $id;
    }

    public function getEntryId(): ?int
    {
        return $this->entryId;
    }

    public function setEntryId(?int $entryId)
    {
        $this->entryId = $entryId;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function toArray(): array
    {
        $toReturn = [
            'content' => htmlspecialchars($this->content),
        ];

        if ($this->id) {
            $toReturn['id'] = $this->id;
        }
        if ($this->entryId) {
            $toReturn['entry_id'] = $this->entryId;
        }

        return $toReturn;
    }
}
