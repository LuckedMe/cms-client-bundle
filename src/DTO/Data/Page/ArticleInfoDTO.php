<?php

declare(strict_types=1);

namespace Vtsot\CmsAdminClientBundle\DTO\Data\Page;

use Vtsot\CmsAdminClientBundle\Contract\Dictionary\PageType;
use Vtsot\CmsAdminClientBundle\DTOFactory\Data\Page\ArticleInfoDTOFactory;

/**
 * @see ArticleInfoDTOFactory
 */
readonly class ArticleInfoDTO
{
    public function __construct(
        public string $id,
        public string $slug,
        public PageType $type,
        public ?string $title,
        public ?string $imageAlt,
        public ?string $image,
        public ?string $description,
        public ?string $content,
        public ?string $sources,
    ) {
    }
}
