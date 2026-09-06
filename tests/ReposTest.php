<?php

declare(strict_types=1);

namespace WyriHaximus\Tests\SubSplitTools;

use ApiClients\Client\GitHub\Error\BasicError as GitHubBasicError;
use ApiClients\Client\GitHub\Schema\BasicError as BasicErrorSchema;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use React\Http\Message\Response;
use React\Http\Message\ResponseException;
use Throwable;
use WyriHaximus\SubSplitTools\Repos;
use WyriHaximus\Tests\SubSplitTools\TestUtilities\GitHubClientStub;
use WyriHaximus\Tests\SubSplitTools\TestUtilities\SuccessfulGet;
use WyriHaximus\Tests\SubSplitTools\TestUtilities\ThrowingGet;
use WyriHaximus\TestUtilities\TestCase;

final class ReposTest extends TestCase
{
    #[Test]
    public function upsertExistingRepository(): void
    {
        $client = new GitHubClientStub(new SuccessfulGet());
        $repos  = new Repos($client);

        $repos->upsert('owner', 'repository');

        self::assertSame(
            [
                [
                    'call' => 'GET /repos/{owner}/{repo}',
                    'params' => [
                        'owner' => 'owner',
                        'repo' => 'repository',
                    ],
                ],
            ],
            $client->getCalls(),
        );
    }

    #[Test]
    #[DataProvider('provideUpsertCreatesRepository')]
    public function upsertCreatesRepository(Throwable $throwable): void
    {
        $client = new GitHubClientStub(new ThrowingGet($throwable));
        $repos  = new Repos($client);

        $repos->upsert('owner', 'repository');

        self::assertSame(
            [
                [
                    'call' => 'GET /repos/{owner}/{repo}',
                    'params' => [
                        'owner' => 'owner',
                        'repo' => 'repository',
                    ],
                ],
                [
                    'call' => 'POST /orgs/{org}/repos',
                    'params' => [
                        'org' => 'owner',
                        'name' => 'repository',
                        'auto_init' => true,
                    ],
                ],
            ],
            $client->getCalls(),
        );
    }

    /** @return iterable<string, array{Throwable}> */
    public static function provideUpsertCreatesRepository(): iterable
    {
        yield 'response exception' => [
            new ResponseException(new Response(Response::STATUS_NOT_FOUND)),
        ];

        yield 'basic error 404' => [
            new GitHubBasicError(404, new BasicErrorSchema('Not Found', null, null, '404')),
        ];
    }

    #[Test]
    public function upsertIgnoresNon404BasicError(): void
    {
        $client = new GitHubClientStub(
            new ThrowingGet(
                new GitHubBasicError(500, new BasicErrorSchema('Internal Server Error', null, null, '500')),
            ),
        );
        $repos  = new Repos($client);

        $repos->upsert('owner', 'repository');

        self::assertSame(
            [
                [
                    'call' => 'GET /repos/{owner}/{repo}',
                    'params' => [
                        'owner' => 'owner',
                        'repo' => 'repository',
                    ],
                ],
            ],
            $client->getCalls(),
        );
    }
}
