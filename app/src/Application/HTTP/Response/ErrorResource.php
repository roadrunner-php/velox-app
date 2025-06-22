<?php

declare(strict_types=1);

namespace App\Application\HTTP\Response;

use Spiral\Http\Exception\ClientException;
use Spiral\RoadRunner\GRPC\Exception\UnauthenticatedException;

/**
 * @property-read \Throwable $data
 */
final class ErrorResource extends JsonResource
{
    public function __construct(\Throwable $data)
    {
        parent::__construct($data);
    }

    #[\Override]
    protected function mapData(): array|\JsonSerializable
    {
        return [
            'message' => $this->data->getMessage(),
            'code' => $this->getCode(),
        ];
    }

    #[\Override]
    protected function getCode(): int
    {
        return match (true) {
            $this->data instanceof ClientException => $this->data->getCode(),
            $this->data instanceof UnauthenticatedException => 401,
            default => 500,
        };
    }
}
