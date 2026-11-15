<?php

declare(strict_types=1);

namespace Vtsot\CmsAdminClientBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;
use Vtsot\CmsAdminClientBundle\DTO\Data\BannerDTO;
use Vtsot\CmsAdminClientBundle\DTOFactory\Data\BannerDTOFactory;
use Vtsot\CmsAdminClientBundle\Exception\CommunicationException;
use Vtsot\CmsAdminClientBundle\Service\Traits\ResolveErrorsDetailsTrait;

final readonly class BannerService
{
    use ResolveErrorsDetailsTrait;

    public function __construct(
        private AuthService $auth,
        private BannerDTOFactory $bannerDTOFactory,

        #[Autowire(param: 'vtsot_cms_admin_client.caching')]
        private bool $caching,

        #[Autowire(service: 'vtsot_cms_admin_client.banner.cache')]
        private CacheInterface $cache,

        #[Autowire(service: 'vtsot_cms_admin_client.content.http_client')]
        private HttpClientInterface $httpClient,
    ) {
    }

    public function getBanner(?bool $throwException = false): ?BannerDTO
    {
        $key = sha1('site-banners-random');

        if (!$this->caching) {
            $this->cache->delete($key);
        }

        return $this->cache->get($key, function () use ($throwException): ?BannerDTO {
            try {
                $token   = $this->auth->login($throwException) ?? throw new CommunicationException('Authentication failed');
                $request = $this->httpClient->request('GET', 'banners/random', [
                    'auth_bearer' => $token->token,
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

            return $this->bannerDTOFactory->fromAPI($response);
        });
    }
}
