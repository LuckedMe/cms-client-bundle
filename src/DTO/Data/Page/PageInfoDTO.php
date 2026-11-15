<?php

declare(strict_types=1);

namespace Vtsot\CmsAdminClientBundle\DTO\Data\Page;

use Vtsot\CmsAdminClientBundle\Contract\Dictionary\PageType;
use Vtsot\CmsAdminClientBundle\DTOFactory\Data\Page\PageInfoDTOFactory;

/**
 * @see PageInfoDTOFactory
 */
readonly class PageInfoDTO
{
    public function __construct(
        public string $id,
        public string $slug,
        public PageType $type,
        public ?bool $isHomepage,
        public ?string $title,
        public ?string $content,
        public ?string $sources,
    ) {
    }
}
