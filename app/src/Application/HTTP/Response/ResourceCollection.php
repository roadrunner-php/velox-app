<?php

declare(strict_types=1);

namespace App\Application\HTTP\Response;

use Psr\Http\Message\ResponseInterface;
use Spiral\DataGrid\Specification\SorterInterface;

class ResourceCollection implements ResourceInterface
{
    use JsonTrait;

    protected array $headers = [];
    private readonly array $args;

    /**
     * @param class-string<ResourceInterface>|\Closure $resource
     */
    public function __construct(
        protected readonly iterable $data,
        protected string|\Closure $resource = JsonResource::class,
        mixed ...$args,
    ) {
        $this->args = $args;
    }

    public function jsonSerialize(): array
    {
        $data = [];
        $resource = $this->getResource();

        if (\is_string($resource)) {
            $resource = static fn(mixed $row, mixed ...$args): ResourceInterface => new $resource($row, ...$args);
        }

        foreach ($this->getData() as $key => $row) {
            $data[$key] = $resource($row, ...\array_values($this->args));
        }

        return $this->wrapData($data);
    }

    public function toResponse(ResponseInterface $response): ResponseInterface
    {
        $response = $this->writeJson($response, $this, $this->getCode());

        foreach ($this->headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        return $response;
    }

    public function withHeaders(array $headers): self
    {
        $self = clone $this;
        $self->headers = $headers;

        return $self;
    }

    protected function getCode(): int
    {
        return 200;
    }

    /**
     * @return class-string<ResourceInterface>|\Closure
     */
    protected function getResource(): string|\Closure
    {
        return $this->resource;
    }

    /**
     * @return iterable<non-empty-string, mixed>
     */
    protected function getData(): iterable
    {
        return $this->data;
    }

    protected function wrapData(array $data): array
    {
        $grid = [];
        $sorters = [];

        return [
            'data' => $data,
            'meta' => [
                'grid' => $grid,
                'sorters' => $this->mapSorters($sorters),
            ],
        ];
    }

    /**
     * @param array<non-empty-string, SorterInterface> $sorters
     * @return non-empty-string[]
     */
    protected function mapSorters(array $sorters)
    {
        return \array_keys($sorters);
    }
}
