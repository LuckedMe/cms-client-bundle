<?php

declare(strict_types=1);

namespace LuckedMe\CmsClientBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;
use LuckedMe\CmsClientBundle\DTO\Data\TokenDTO;
use LuckedMe\CmsClientBundle\DTOFactory\Data\TokenDTOFactory;
use LuckedMe\CmsClientBundle\Exception\CommunicationException;
use LuckedMe\CmsClientBundle\Service\Traits\ResolveErrorsDetailsTrait;

final readonly class AuthService
{
    use ResolveErrorsDetailsTrait;

    public function __construct(
        #[Autowire(service: 'lucked_me_cms_client.auth.http_client')]
        private HttpClientInterface $httpClient,

        #[Autowire(param: 'lucked_me_cms_client.client_username')]
        private string $username,

        #[Autowire(param: 'lucked_me_cms_client.client_password')]
        private string $password,

        private TokenDTOFactory $tokenDTOFactory,
    ) {
    }

    public function login(?bool $throwException = false): ?TokenDTO
    {
        try {
            $request = $this->httpClient->request('POST', 'login', [
                'json' => [
                    'username' => $this->username,
                    'password' => $this->password,
                ],
            ]);

            $response = $request->toArray();
        } catch (HttpExceptionInterface|TransportExceptionInterface|DecodingExceptionInterface|Throwable $e) {
            $response = $e instanceof HttpExceptionInterface ? $e->getResponse() : null;
            $code     = $e instanceof HttpExceptionInterface ? $response->getStatusCode() : 0;
            $errors   = $response ? $this->errorsDetailsAsString($response) : null;

            if ($throwException) {
                throw new CommunicationException($errors ?? $e->getMessage(), $code, $e);
            }

            return null;
        }

        if (!($response['token'] ?? null)
            || !($response['refresh_token'] ?? null)
            || !($response['refresh_token_expiration'] ?? null)
        ) {
            if ($throwException) {
                throw new CommunicationException('Incorrect response');
            }

            return null;
        }

        return $this->tokenDTOFactory->fromAPI($response);
    }
}
