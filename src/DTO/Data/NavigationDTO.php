<?php

declare(strict_types=1);

namespace Vtsot\CmsAdminClientBundle\DTO\Data;

/**
 * @see NavigationDTOFactory
 */
readonly class NavigationDTO
{
    public function __construct(
        public ?string $title,
        public ?LinkDTO $link,
        public array $children = [],
    ) {
    }
}
