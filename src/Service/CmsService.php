<?php

declare(strict_types=1);

namespace Vtsot\CmsAdminClientBundle\Service;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Error\Error as TwigError;
use Vtsot\CmsAdminClientBundle\Contract\Dictionary\PageType;

use function sprintf;

readonly class CmsService
{
    public function __construct(
        private SiteService $siteService,
        private PageService $pageService,
        private UrlGeneratorInterface $urlGenerator,
        private Environment $twig,
    ) {
    }

    /**
     * @throws InvalidArgumentException
     * @throws TwigError
     */
    public function handleRequest(?string $slug, Request $request): Response
    {
        if ((int) $request->query->get('page') === 1) {
            $params = $request->attributes->get('_route_params', []) + $request->query->all();
            unset($params['page']);

            return new RedirectResponse($this->urlGenerator->generate($request->attributes->get('_route'), $params));
        }

        if (!$site = $this->siteService->getSite()) {
            throw new NotFoundHttpException('Site Not Found');
        }

        // redirect from slug homepage to homepage w/o slug
        if ($slug !== null && $site->homepage !== null && $slug === $site->homepage) {
            return new RedirectResponse($this->urlGenerator->generate('web.homepage'), Response::HTTP_MOVED_PERMANENTLY);
        }

        if (!$page = $this->pageService->getPage($slug ?? $site->homepage)) {
            throw new NotFoundHttpException(sprintf('Page "%s" Not Found', $slug ?? 'homepage'));
        }

        // redirect page except homepage
        if ($page->redirect && !$page->isHomepage) {
            return new RedirectResponse($page->redirect, Response::HTTP_MOVED_PERMANENTLY);
        }

        $view = match ($page->type) {
            PageType::Article => '@VtsotCmsAdminClient/web/default/article.html.twig',
            default           => '@VtsotCmsAdminClient/web/default/page.html.twig',
        };

        return new Response($this->twig->render($view, [
            'site' => $site,
            'page' => $page,
        ]));
    }
}
