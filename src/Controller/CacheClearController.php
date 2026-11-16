<?php

declare(strict_types=1);

namespace Vtsot\CmsAdminClientBundle\Controller;

use Exception;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
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

        $php = new PhpExecutableFinder()->find();
        if (false === $php) {
            throw new RuntimeException('PHP executable not found.');
        }

        $env     = $kernel->getEnvironment();
        $console = $kernel->getProjectDir() . '/bin/console';

        $pools = [
            'vtsot_cms_admin_client.cache',
            'vtsot_cms_admin_client.banner.cache',
            ...$extraPools,
        ];

        // Every command runs in its own process with a fresh kernel. Running
        // cache:clear in-process (several times) on the live kernel breaks it:
        // the first run switches the kernel to the temporary "pro_" cache
        // directory, so the next command fails with
        // 'Unable to write in the ".../var/cache/pro_" directory'.
        // The environment is the one of the running kernel (the old "--env"
        // option was ignored by the in-process application anyway).
        $commands = [
            ['cache:clear'],
            ['cache:pool:clear', ...$pools],
        ];

        $output = '';
        $failed = false;
        foreach ($commands as $arguments) {
            $process = new Process(
                [$php, $console, ...$arguments, '--env=' . $env, '--no-interaction'],
                $kernel->getProjectDir(),
                timeout: 120,
            );
            $process->run();

            $output .= '$ ' . $process->getCommandLine() . "\n"
                . $process->getOutput() . $process->getErrorOutput();
            $failed = $failed || !$process->isSuccessful();
        }

        return new Response(
            '<pre>' . htmlspecialchars($output) . '</pre>',
            $failed ? Response::HTTP_INTERNAL_SERVER_ERROR : Response::HTTP_OK,
        );
    }
}
