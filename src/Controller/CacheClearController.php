<?php

declare(strict_types=1);

namespace LuckedMe\CmsClientBundle\Controller;

use Exception;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\CacheClearer\Psr6CacheClearer;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;

use function array_filter;
use function array_unique;
use function array_values;
use function implode;
use function sprintf;
use function Symfony\Component\String\s;

#[AsController]
final class CacheClearController
{
    /**
     * @param string[] $extraPools additional cache pools to clear (`cache_clear_pools` option)
     *
     * @throws Exception
     */
    #[Route(path: '/lucked-me-cms-client/cache-clear', name: 'lucked_me_cms_client.cache.clear')]
    public function __invoke(
        Request $request,
        KernelInterface $kernel,
        #[Autowire(param: 'lucked_me_cms_client.site_token')]
        string $token,
        #[Autowire(param: 'lucked_me_cms_client.cache_clear_pools')]
        array $extraPools = [],
        #[Autowire(service: 'cache.global_clearer')]
        ?Psr6CacheClearer $poolClearer = null,
    ): JsonResponse {

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
            'lucked_me_cms_client.cache',
            'lucked_me_cms_client.banner.cache',
            ...$extraPools,
        ];

        // cache:pool:clear fails on an unknown pool (and then skips the rest),
        // so keep only the pools that exist in this application
        $pools = array_values(array_unique(array_filter(
            $pools,
            static fn (string $pool): bool => $poolClearer?->hasPool($pool) ?? false,
        )));

        // Every command runs in its own process with a fresh kernel. Running
        // cache:clear in-process (several times) on the live kernel breaks it:
        // the first run switches the kernel to the temporary "pro_" cache
        // directory, so the next command fails with
        // 'Unable to write in the ".../var/cache/pro_" directory'.
        // The environment is the one of the running kernel (the old "--env"
        // option was ignored by the in-process application anyway).
        $commands = [['cache:clear']];
        if ([] !== $pools) {
            $commands[] = ['cache:pool:clear', ...$pools];
        }

        $output = [];
        $failed = false;
        foreach ($commands as $arguments) {
            $process = new Process(
                [$php, $console, ...$arguments, '--env=' . $env, '--no-interaction'],
                $kernel->getProjectDir(),
                timeout: 120,
            );
            $process->run();

            $cleanOutput = static fn($str) => s($str)
                ->replace('\u0022', '')
                ->replaceMatches('/\s+/', ' ')
                ->replaceMatches('#\s*//+\s*#', ' ')
                ->trim()
                ->toString();

            $output[] = [
                'code'    => $process->getExitCodeText(),
                'command' => sprintf('%s %s', implode(' ', $arguments), '--env=' . $env),
                'output'  => $cleanOutput($process->getOutput()),
                'errors'  => $cleanOutput($process->getErrorOutput()),
            ];

            $failed = $failed || !$process->isSuccessful();
        }

        return new JsonResponse(
            data: $output,
            status: $failed ? Response::HTTP_MULTI_STATUS : Response::HTTP_OK,
        );
    }
}
