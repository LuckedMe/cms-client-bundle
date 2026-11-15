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
use Vtsot\CmsAdminClientBundle\Contract\Dictionary\PageType;
use Vtsot\CmsAdminClientBundle\DTO\Collection\PageInfoCollectionDTO;
use Vtsot\CmsAdminClientBundle\DTO\Data\Page\ArticleDetailsDTO;
use Vtsot\CmsAdminClientBundle\DTO\Data\Page\PageDetailsDTO;
use Vtsot\CmsAdminClientBundle\DTOFactory\Data\Page\ArticleDetailsDTOFactory;
use Vtsot\CmsAdminClientBundle\DTOFactory\Data\Page\ArticleInfoDTOFactory;
use Vtsot\CmsAdminClientBundle\DTOFactory\Data\Page\PageDetailsDTOFactory;
use Vtsot\CmsAdminClientBundle\DTOFactory\Data\Page\PageInfoDTOFactory;
use Vtsot\CmsAdminClientBundle\Exception\CommunicationException;
use Vtsot\CmsAdminClientBundle\Service\Traits\ResolveErrorsDetailsTrait;

final readonly class PageService
{
    use ResolveErrorsDetailsTrait;

    public function __construct(
        private AuthService $auth,
        private PageInfoDTOFactory $pageInfoDTOFactory,
        private PageDetailsDTOFactory $pageDetailsDTOFactory,
        private ArticleInfoDTOFactory $articleInfoDTOFactory,
        private ArticleDetailsDTOFactory $articleDetailsDTOFactory,

        #[Autowire(param: 'vtsot_cms_admin_client.caching')]
        private bool $caching,

        #[Autowire(service: 'vtsot_cms_admin_client.cache')]
        private CacheInterface $cache,

        #[Autowire(service: 'vtsot_cms_admin_client.content.http_client')]
        private HttpClientInterface $httpClient,
    ) {
    }

    public function getArticles(array $query = [], ?bool $throwException = false): PageInfoCollectionDTO
    {
        $query['type'] = PageType::Article->value;

        return $this->getPages($query, $throwException);
    }

    public function getPages(array $query = [], ?bool $throwException = false): PageInfoCollectionDTO
    {
        $key = sha1('site-pages' . ($query ? '-' . json_encode($query) : ''));

        if (!$this->caching) {
            $this->cache->delete($key);
        }

        return $this->cache->get($key, function () use ($query, $throwException): PageInfoCollectionDTO {
            try {
                $token   = $this->auth->login($throwException) ?? throw new CommunicationException('Authentication failed');
                $request = $this->httpClient->request('GET', 'pages', [
                    'auth_bearer' => $token->token,
                    'query'       => $query,
                ]);

                $response = $request->toArray();
            } catch (HttpExceptionInterface|TransportExceptionInterface|DecodingExceptionInterface|Throwable $e) {
                $response = $e instanceof HttpExceptionInterface ? $e->getResponse() : null;
                $code     = $e instanceof HttpExceptionInterface ? $response->getStatusCode() : 0;
                $errors   = $response ? $this->errorsDetailsAsString($response) : null;

                if ($throwException) {
                    throw new CommunicationException($errors ?? $e->getMessage(), $code, $e);
                }

                return PageInfoCollectionDTO::fromArray([]);
            }

            $items = [];

            foreach ($response['items'] ?? [] as $item) {
                $items[] = match ($item['type']) {
                    PageType::Article->value => $this->articleInfoDTOFactory->fromAPI($item),
                    default                  => $this->pageInfoDTOFactory->fromAPI($item),
                };
            }

            return PageInfoCollectionDTO::fromArray(
                items: $items,
                paginator: $response['meta']['paginator'] ?? [],
                filters: $response['meta']['filters'] ?? [],
            );
        });
    }

    public function getPage(string $slug, ?bool $throwException = false): PageDetailsDTO|ArticleDetailsDTO|null
    {
        $key = sha1('site-page-slug-' . $slug);

        if (!$this->caching) {
            $this->cache->delete($key);
        }

        return $this->cache->get($key, function () use ($slug, $throwException): PageDetailsDTO|ArticleDetailsDTO|null {
            try {
                $token   = $this->auth->login($throwException) ?? throw new CommunicationException('Authentication failed');
                $request = $this->httpClient->request('GET', "pages/{$slug}", [
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

            return match ($response['type'] ?? null) {
                PageType::Page->value    => $this->pageDetailsDTOFactory->fromAPI($response),
                PageType::Article->value => $this->articleDetailsDTOFactory->fromAPI($response),
                default                  => null,
            };
        });
    }
}
