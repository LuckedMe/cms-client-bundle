<?php

declare(strict_types=1);

namespace Vtsot\CmsAdminClientBundle\DTO\Collection\Meta;

final readonly class CollectionMetaDTO
{
    /** @param mixed[]|null $debug */
    public function __construct(
        public CollectionPaginatorDTO $paginator,
        public ?CollectionFiltersDTO $filters,
        public ?array $debug = null,
    ) {
    }
}
