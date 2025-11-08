<?php

declare(strict_types=1);

namespace App\Module\Github;

use App\Module\Github\Contributors\ContributorsBootloader;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Symfony\Component\HttpClient\Psr18Client;

final class GithubBootloader extends Bootloader
{
    public function defineDependencies(): array
    {
        return [
            ContributorsBootloader::class,
        ];
    }

    public function defineSingletons(): array
    {
        return [
            RequestFactoryInterface::class => static fn(): RequestFactoryInterface => new Psr17Factory(),

            Client::class => static fn(
                EnvironmentInterface $env,
                RequestFactoryInterface $requestFactory,
            ): Client
                => new Client(
                httpClient: (new Psr18Client())->withOptions([
                    'base_uri' => $env->get('GITHUB_API_BASE_URI', 'https://api.github.com/'),
                    'headers' => [
                        'User-Agent' => $env->get('GITHUB_USER_AGENT', 'VeloxApp/1.0'),
                        'Authorization' => 'Bearer ' . $env->get('GITHUB_TOKEN', ''),
                        'Accept' => 'application/vnd.github.v3+json',
                    ],
                    'timeout' => (float) $env->get('GITHUB_API_TIMEOUT', 30),
                ]),
                requestFactory: $requestFactory,
                repository: $env->get('GITHUB_REPOSITORY', 'roadrunner-php/velox-app'),
            ),

            // GitHub Client for discovery
            DiscoveryClient::class => static fn(
                RequestFactoryInterface $requestFactory,
                EnvironmentInterface $env,
            ): DiscoveryClient
                => new DiscoveryClient(
                httpClient: (new Psr18Client())->withOptions([
                    'base_uri' => $env->get('GITHUB_API_BASE_URI', 'https://api.github.com/'),
                    'headers' => [
                        'User-Agent' => $env->get('GITHUB_USER_AGENT', 'VeloxApp/1.0'),
                        'Authorization' => 'Bearer ' . $env->get('GITHUB_TOKEN', ''),
                        'Accept' => 'application/vnd.github.v3+json',
                    ],
                    'timeout' => (float) $env->get('GITHUB_API_TIMEOUT', 30),
                ]),
                requestFactory: $requestFactory,
                token: $env->get('GITHUB_TOKEN'),
            ),
        ];
    }
}
