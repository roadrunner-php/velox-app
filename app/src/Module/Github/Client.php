<?php

declare(strict_types=1);

namespace App\Module\Github;

use App\Module\Github\Contributors\Dto\Contributor;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Spiral\Http\Exception\ClientException\ServerErrorException;

final readonly class Client
{
    public function __construct(
        private ClientInterface $httpClient,
        private RequestFactoryInterface $requestFactory,
        private string $repository = 'roadrunner-php/velox-app',
    ) {}

    /**
     * @return array<Contributor>
     */
    public function getContributors(
        int $perPage = 30,
        int $page = 1,
    ): array {
        $url = $this->buildContributorsUrl($perPage, $page);

        $request = $this->requestFactory->createRequest('GET', $url);
        $response = $this->httpClient->sendRequest($request);

        $data = \json_decode($response->getBody()->getContents(), true);

        if (!\is_array($data)) {
            throw new ServerErrorException('Invalid response format from GitHub API');
        }

        return \array_map(
            static fn(array $contributorData): Contributor => Contributor::fromGithubApiResponse($contributorData),
            $data,
        );
    }

    private function buildContributorsUrl(int $perPage, int $page): string
    {
        $queryString = \http_build_query([
            'per_page' => $perPage,
            'page' => $page,
        ]);

        return "repos/{$this->repository}/contributors?{$queryString}";
    }
}
