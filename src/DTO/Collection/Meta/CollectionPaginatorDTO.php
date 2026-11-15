<?php

declare(strict_types=1);

namespace Vtsot\CmsAdminClientBundle\DTO\Collection\Meta;

final readonly class CollectionPaginatorDTO
{
    public function __construct(
        public int $page,
        public int $perPage,
        public int $total,
    ) {
    }
}
