<?php
class OperationResult
{
    function __construct(
        public readonly bool $success,
        public readonly string $message = "",
    ) {}
}
