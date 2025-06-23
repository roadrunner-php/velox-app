<?php

declare(strict_types=1);

namespace App\Application\HTTP\Response;

use Spiral\Filters\Exception\ValidationException;

/**
 * @extends JsonResource<ValidationException>
 */
final class ValidationResource extends JsonResource
{
    public function __construct(ValidationException $data)
    {
        parent::__construct($data);
    }

    #[\Override]
    protected function getCode(): int
    {
        return $this->data->getCode();
    }

    #[\Override]
    protected function mapData(): array|\JsonSerializable
    {
        return [
            'message' => 'validation_errors',
            'code' => $this->data->getCode(),
            'errors' => $this->data,
        ];
    }
}
