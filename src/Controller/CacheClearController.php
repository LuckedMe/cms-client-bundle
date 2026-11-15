<?php

declare(strict_types=1);

namespace Vtsot\CmsAdminClientBundle\Controller;

use Exception;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
final class CacheClearController
{
    /**
     * @param string[] $extraPools additional cache pools to clear (`cache_clear_pools` option)
     *
     * @throws Exception
     */
    #[Route(path: '/cms-admin-client/cache-clear', name: 'cms_admin_client.cache.clear')]
    public function __invoke(
        Request $request,
        KernelInterface $kernel,
        #[Autowire(param: 'vtsot_cms_admin_client.site_token')]
        string $token,
        #[Autowire(param: 'vtsot_cms_admin_client.cache_clear_pools')]
        array $extraPools = [],
    ): Response {
        if (!$request->headers->get('X-API-KEY')
            || $request->headers->get('X-API-KEY') !== $token
        ) {
            throw new AccessDeniedHttpException('Access denied.');
        }

        $application = new Application($kernel);
        $application->setAutoExit(false);
        $output = new BufferedOutput();

        $pools = [
            'vtsot_cms_admin_client.cache',
            'vtsot_cms_admin_client.banner.cache',
            ...$extraPools,
        ];

        $commands   = [];
        $commands[] = ['command' => 'cache:clear', '--env' => 'dev'];
        $commands[] = ['command' => 'cache:clear', '--env' => 'prod'];
        $commands[] = ['command' => 'cache:pool:clear', '--env' => 'prod', 'pools' => $pools];
        $commands[] = ['command' => 'cache:pool:clear', '--env' => 'dev', 'pools' => $pools];

        foreach ($commands as $arrayInput) {
            $application->run(new ArrayInput($arrayInput), $output);
        }

        return new Response('<pre>' . $output->fetch() . '</pre>');
    }
}
