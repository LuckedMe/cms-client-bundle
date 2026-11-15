<?php

declare(strict_types=1);

namespace Vtsot\CmsAdminClientBundle\DTO\Collection;

readonly class SitemapCollectionDTO
{
    public function __construct(
        /** @var SitemapItemDTO[] */
        public array $items,
    ) {
    }
}
