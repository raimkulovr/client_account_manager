<?php
class Company {
    function __construct(
        public readonly int $id, 
        public readonly string $name,
        public readonly string $description,
    ) {}

    public static function fromArray($array) {
        if (is_array($array)) {
            return new self($array['id'], $array['name'], $array['description'] ?? "");
        }
        return null;
    }
}