<?php

declare(strict_types=1);

namespace App\Module\Velox\Plugin\Exception;

final class PluginNotFoundException extends \Exception
{
    public function __construct(string $pluginName)
    {
        parent::__construct("Plugin '{$pluginName}' not found");
    }
}
