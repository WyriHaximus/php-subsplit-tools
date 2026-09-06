<?php

declare(strict_types=1);

namespace WyriHaximus\Tests\SubSplitTools\TestUtilities;

use ApiClients\Client\GitHub\ClientInterface;
use ApiClients\Client\GitHub\OperationsInterface;
use ApiClients\Contracts\OpenAPI\WebHooksInterface;
use LogicException;

final class GitHubClientStub implements ClientInterface
{
    /** @var list<array{call: string, params: array<string, mixed>}> */
    private array $calls = [];

    public function __construct(private readonly GetBehavior $getBehavior)
    {
    }

    /**
     * @param array<string, mixed> $params
     *
     * @phpstan-ignore method.childParameterType
     */
    public function call(string $call, array $params = []): string
    {
        $this->calls[] = ['call' => $call, 'params' => $params];

        if ($call === 'GET /repos/{owner}/{repo}' && $this->getBehavior instanceof ThrowingGet) {
            throw $this->getBehavior->throwable;
        }

        return '';
    }

    public function operations(): OperationsInterface
    {
        throw new LogicException('Not expected to be called');
    }

    public function webHooks(): WebHooksInterface
    {
        throw new LogicException('Not expected to be called');
    }

    /** @return list<array{call: string, params: array<string, mixed>}> */
    public function getCalls(): array
    {
        return $this->calls;
    }
}
