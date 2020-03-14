<?php

declare(strict_types=1);

namespace midorikocak\dictionary;

use function array_map;
use function htmlspecialchars;

class Title implements ArrayableInterface
{
    private ?int $id;
    private string $title;

    /** @var Entry[] */
    private array $entries = [];

    public function __construct(string $title, ?int $id = null)
    {
        $this->id = $id;
        $this->title = $title;
    }

    public function addEntry(Entry $entry): void
    {
        $entry->setTitleId($this->id);
        $this->entries [] = $entry;
    }

    /**
     * @return Entry[]
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id)
    {
        $this->id = $id;

        /**
         * @var Entry $entry
         */
        array_map(fn($entry) => $entry->setTitleId($this->id), $this->entries);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    public function toArray(): array
    {
        $toReturn = [
            'title' => htmlspecialchars($this->title),
        ];

        if ($this->id) {
            $toReturn['id'] = $this->id;
        }

        if (!empty($this->entries)) {
            $toReturn['entries'] = array_map(fn($entry) => $entry->toArray(), $this->entries);
        }

        return $toReturn;
    }
}
