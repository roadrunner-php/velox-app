<?php

declare(strict_types=1);

namespace App\Application\HTTP\Response;

use Psr\Http\Message\ResponseInterface;

/**
 * @template TData of array|\JsonSerializable
 */
class JsonResource implements ResourceInterface
{
    use JsonTrait;

    /** @var TData */
    protected readonly mixed $data;

    protected array $headers = [];

    /**
     * @param TData $data
     */
    public function __construct(mixed $data = [])
    {
        $this->data = $data;
    }

    public function toResponse(ResponseInterface $response): ResponseInterface
    {
        $response = $this->writeJson($response, $this, $this->getCode());

        foreach ($this->headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }

    public function jsonSerialize(): array
    {
        $data = $this->mapData();

        if ($data instanceof \JsonSerializable) {
            $data = $data->jsonSerialize();
        }

        foreach ($data as $key => $value) {
            if ($value instanceof ResourceInterface) {
                $data[$key] = $value->jsonSerialize();
            }
        }

        return $this->wrapData($data);
    }

    public function withHeaders(array $headers): self
    {
        $self = clone $this;
        $self->headers = $headers;

        return $self;
    }

    /**
     * @return TData
     */
    protected function mapData(): array|\JsonSerializable
    {
        return $this->data;
    }

    protected function getCode(): int
    {
        return 200;
    }

    protected function wrapData(array $data): array
    {
        return $data;
    }
}
