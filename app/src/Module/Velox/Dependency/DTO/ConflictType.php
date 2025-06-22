<?php

declare(strict_types=1);

namespace App\Module\Velox\Dependency\DTO;

enum ConflictType: string
{
    case CircularDependency = 'circular_dependency';
    case MissingDependency = 'missing_dependency';
    case VersionConflict = 'version_conflict';
    case ResourceConflict = 'resource_conflict';
    case IncompatiblePlugin = 'incompatible_plugin';
    case DuplicatePlugin = 'duplicate_plugin';
}
