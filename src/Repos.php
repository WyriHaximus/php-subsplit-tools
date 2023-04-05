<?php

declare(strict_types=1);

namespace WyriHaximus\SubSplitTools;

use ApiClients\Client\GitHub\ClientInterface;
use ApiClients\Client\GitHub\Error\BasicError;
use React\Http\Message\ResponseException;

final class Repos
{
    public function __construct(private readonly ClientInterface $client)
    {
    }

    public function upsert(string $owner, string $repository): void
    {
        try {
            $this->client->call('GET /repos/{owner}/{repo}', [
                'owner' => $owner,
                'repo' => $repository,
            ]);
        } catch (ResponseException | BasicError $basicError) {
            if ($basicError instanceof ResponseException || $basicError->getCode() === 404) {
                $this->client->call('POST /orgs/{org}/repos', [
                    'org' => $owner,
                    'name' => $repository,
                    'auto_init' => true,
                ]);
            }
        }
    }
}
