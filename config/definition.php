<?php

declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition): void {
    $definition->rootNode()
        ->children()
            ->scalarNode('api_auth_url')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('api_content_url')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('locale')->defaultValue('en_GB')->end()
            ->scalarNode('site_id')->isRequired()->end()
            ->scalarNode('site_token')->isRequired()->end()
            ->scalarNode('client_username')->isRequired()->cannotBeEmpty()->end()
            ->scalarNode('client_password')->isRequired()->cannotBeEmpty()->end()
            ->booleanNode('caching')->defaultTrue()->end()
            ->arrayNode('cache_clear_pools')
                ->info('Extra cache pools to clear on /cache-clear')
                ->scalarPrototype()->end()
                ->defaultValue([])
            ->end()
            ->integerNode('cache_default_lifetime')->defaultValue(86400)->min(0)->end()
            ->integerNode('cache_banner_lifetime')->defaultValue(3600)->min(0)->end()
        ->end();
};
