<?php

declare(strict_types=1);

namespace Vtsot\CmsAdminClientBundle\Tests\Service;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;
use Vtsot\CmsAdminClientBundle\DTOFactory\Data\TokenDTOFactory;
use Vtsot\CmsAdminClientBundle\Exception\CommunicationException;
use Vtsot\CmsAdminClientBundle\Service\AuthService;

use const JSON_THROW_ON_ERROR;

final class AuthServiceTest extends TestCase
{
    private const array TOKENS = [
        'token'                    => 'jwt',
        'refresh_token'            => 'refresh',
        'refresh_token_expiration' => 1893456000,
    ];

    public function testLoginSendsCredentialsAndReturnsTokenDto(): void
    {
        $response = new JsonMockResponse(self::TOKENS);

        $token = $this->createAuth($response)->login();

        self::assertNotNull($token);
        self::assertSame('jwt', $token->token);
        self::assertSame('refresh', $token->refreshToken);
        self::assertSame(1893456000, $token->refreshTokenExpiration);
        self::assertSame('POST', $response->getRequestMethod());
        self::assertSame('https://api.cms-admin.local/sites/auth/login', $response->getRequestUrl());
        self::assertSame(
            ['username' => 'user@example.com', 'password' => 'secret'],
            json_decode($response->getRequestOptions()['body'], true, 512, JSON_THROW_ON_ERROR),
        );
    }

    public function testLoginReturnsNullOnFailureByDefault(): void
    {
        $auth = $this->createAuth(new JsonMockResponse(['message' => 'Invalid credentials.'], ['http_code' => 401]));

        self::assertNull($auth->login());
    }

    public function testLoginThrowsWithApiMessageWhenRequestIsRejected(): void
    {
        $auth = $this->createAuth(new JsonMockResponse(['code' => 401, 'message' => 'Invalid credentials.'], ['http_code' => 401]));

        try {
            $auth->login(true);
            self::fail('Expected a CommunicationException.');
        } catch (CommunicationException $e) {
            self::assertSame(401, $e->getCode());
            self::assertStringContainsString('Invalid credentials.', $e->getMessage());
            self::assertNotNull($e->getPrevious());
        }
    }

    public function testLoginThrowsOnServerErrorWithHtmlBody(): void
    {
        $auth = $this->createAuth(new MockResponse('<html>Internal Server Error</html>', ['http_code' => 500]));

        try {
            $auth->login(true);
            self::fail('Expected a CommunicationException.');
        } catch (CommunicationException $e) {
            self::assertSame(500, $e->getCode());
            self::assertStringContainsString('500', $e->getMessage());
        }
    }

    public function testLoginThrowsOnTransportFailure(): void
    {
        $auth = $this->createAuth(new MockResponse(info: ['error' => 'Connection refused']));

        $this->expectException(CommunicationException::class);
        $this->expectExceptionMessage('Connection refused');

        $auth->login(true);
    }

    public function testLoginThrowsOnThrownTransportException(): void
    {
        $auth = $this->createAuth(static fn () => throw new TransportException('DNS failure'));

        $this->expectException(CommunicationException::class);
        $this->expectExceptionMessage('DNS failure');

        $auth->login(true);
    }

    public function testLoginThrowsOnInvalidJsonInSuccessfulResponse(): void
    {
        $auth = $this->createAuth(new MockResponse('not json', ['http_code' => 200]));

        $this->expectException(CommunicationException::class);

        $auth->login(true);
    }

    public function testLoginThrowsWhenResponseLacksTokens(): void
    {
        $auth = $this->createAuth(new JsonMockResponse(['token' => 'jwt']));

        $this->expectException(CommunicationException::class);
        $this->expectExceptionMessage('Incorrect response');

        $auth->login(true);
    }

    public function testLoginReturnsNullWhenResponseLacksTokensByDefault(): void
    {
        self::assertNull($this->createAuth(new JsonMockResponse(['token' => 'jwt']))->login());
    }

    private function createAuth(callable|MockResponse $response): AuthService
    {
        return new AuthService(
            new MockHttpClient($response, 'https://api.cms-admin.local/sites/auth/'),
            'user@example.com',
            'secret',
            new TokenDTOFactory(),
        );
    }
}
