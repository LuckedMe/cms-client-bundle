<?php

declare(strict_types=1);

namespace LuckedMe\CmsClientBundle\DTO\Data\Page;

use LuckedMe\CmsClientBundle\Contract\Dictionary\PageType;
use LuckedMe\CmsClientBundle\DTOFactory\Data\Page\PageInfoDTOFactory;

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
