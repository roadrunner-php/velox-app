<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Discovery\Service;

use App\Module\Velox\Plugin\Discovery\DTO\PluginManifest;
use App\Module\Velox\Plugin\Discovery\Exception\ManifestValidationException;
use App\Module\Velox\Plugin\DTO\PluginCategory;
use Symfony\Component\Yaml\Yaml;

/**
 * Parses and validates .velox.yaml manifest files
 */
final readonly class ManifestParserService
{
    public function parse(string $content, string $owner, string $repositoryName, string $version): PluginManifest
    {
        try {
            $data = Yaml::parse($content);
        } catch (\Exception $e) {
            throw new ManifestValidationException(
                "Invalid YAML syntax: {$e->getMessage()}",
                $repositoryName,
                ['yaml' => $e->getMessage()],
            );
        }

        if (!\is_array($data)) {
            throw new ManifestValidationException(
                'Manifest must be a YAML object',
                $repositoryName,
            );
        }

        $this->validate($data, $repositoryName);
        $data['owner'] = $owner;

        return PluginManifest::fromArray($data, $repositoryName, $version);
    }

    private function validate(array $data, string $repositoryName): void
    {
        // Validate required fields
        $this->validateRequired($data, $repositoryName, 'name');
        $this->validateRequired($data, $repositoryName, 'description');
        $this->validateRequired($data, $repositoryName, 'category');

        // Validate name format
        if (!$this->isValidName($data['name'] ?? '')) {
            throw ManifestValidationException::invalidFormat(
                $repositoryName,
                'name',
                'lowercase alphanumeric with hyphens (3-50 chars)',
            );
        }

        // Validate description length
        $description = $data['description'] ?? '';
        if (\strlen($description) < 10 || \strlen($description) > 500) {
            throw ManifestValidationException::invalidFormat(
                $repositoryName,
                'description',
                '10-500 characters',
            );
        }

        // Validate category
        if (!$this->isValidCategory($data['category'] ?? '')) {
            throw ManifestValidationException::invalidFormat(
                $repositoryName,
                'category',
                'one of: ' . \implode(', ', $this->getValidCategories()),
            );
        }

        // Validate dependencies array
        if (isset($data['dependencies']) && !\is_array($data['dependencies'])) {
            throw ManifestValidationException::invalidFormat(
                $repositoryName,
                'dependencies',
                'array of plugin names',
            );
        }
    }

    private function validateRequired(array $data, string $repositoryName, string $field): void
    {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw ManifestValidationException::missingRequiredField($repositoryName, $field);
        }
    }

    private function isValidName(string $name): bool
    {
        return \preg_match('/^[a-z0-9-]{3,50}$/', $name) === 1;
    }

    private function isValidVersion(string $version): bool
    {
        return \preg_match('/^v\d+\.\d+\.\d+$/', $version) === 1;
    }

    private function isValidCategory(string $category): bool
    {
        return \in_array($category, $this->getValidCategories(), true);
    }

    /**
     * @return array<string>
     */
    private function getValidCategories(): array
    {
        return \array_map(
            static fn(PluginCategory $cat) => $cat->value,
            PluginCategory::cases(),
        );
    }
}
