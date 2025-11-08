<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery\Exception;

/**
 * Thrown when plugin manifest validation fails
 */
final class ManifestValidationException extends DiscoveryException
{
    public function __construct(
        string $message,
        public readonly string $repository,
        public readonly array $errors = [],
    ) {
        parent::__construct($message);
    }

    public static function missingRequiredField(string $repository, string $field): self
    {
        return new self(
            "Missing required field: {$field}",
            $repository,
            [$field => 'This field is required'],
        );
    }

    public static function invalidFormat(string $repository, string $field, string $expected): self
    {
        return new self(
            "Invalid format for field {$field}: expected {$expected}",
            $repository,
            [$field => "Expected format: {$expected}"],
        );
    }

    public static function invalidDependency(string $repository, string $dependency): self
    {
        return new self(
            "Invalid dependency: {$dependency}",
            $repository,
            ['dependencies' => "Unknown plugin: {$dependency}"],
        );
    }
}
