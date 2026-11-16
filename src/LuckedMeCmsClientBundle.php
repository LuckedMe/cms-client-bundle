<?php

declare(strict_types=1);

namespace LuckedMe\CmsClientBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class LuckedMeCmsClientBundle extends AbstractBundle
{
    private const string DEFAULT_LOCALE      = 'en_GB';
    private const int CACHE_DEFAULT_LIFETIME = 86400;
    private const int CACHE_BANNER_LIFETIME  = 3600;
    protected string $extensionAlias         = 'lucked_me_cms_client';

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // The framework extension resolves these values while it loads, before this
        // bundle's own configuration is processed, so read the raw configs here.
        $config = array_merge([], ...$builder->getExtensionConfig($this->extensionAlias));

        $builder->prependExtensionConfig('framework', [
            'http_client' => [
                'scoped_clients' => [
                    'lucked_me_cms_client.auth.http_client' => [
                        'base_uri'    => $config['api_auth_url'] ?? null,
                        'verify_peer' => false,
                        'headers'     => [
                            'Content-Type'    => 'application/json',
                            'Accept-Language' => $config['locale'] ?? self::DEFAULT_LOCALE,
                            'X-Site-Id'       => $config['site_id'] ?? '',
                            'X-Site-Token'    => $config['site_token'] ?? '',
                        ],
                    ],
                    'lucked_me_cms_client.content.http_client' => [
                        'base_uri'    => $config['api_content_url'] ?? null,
                        'verify_peer' => false,
                        'headers'     => [
                            'Content-Type'    => 'application/json',
                            'Accept-Language' => $config['locale'] ?? self::DEFAULT_LOCALE,
                        ],
                    ],
                ],
            ],
            'cache' => [
                'pools' => [
                    'lucked_me_cms_client.cache'        => ['default_lifetime' => $config['cache_default_lifetime'] ?? self::CACHE_DEFAULT_LIFETIME],
                    'lucked_me_cms_client.banner.cache' => ['default_lifetime' => $config['cache_banner_lifetime'] ?? self::CACHE_BANNER_LIFETIME],
                ],
            ],
        ]);
    }

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        foreach ($config as $key => $value) {
            $container->parameters()->set('lucked_me_cms_client.' . $key, $value);
        }

        $container->import('../config/services.yaml');
    }
}
