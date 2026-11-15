<?php

declare(strict_types=1);

namespace Vtsot\CmsAdminClientBundle\DTO\Collection;

use Vtsot\CmsAdminClientBundle\DTO\Data\Page\ArticleInfoDTO;
use Vtsot\CmsAdminClientBundle\DTO\Data\Page\PageInfoDTO;

final readonly class PageInfoCollectionDTO extends AbstractCollectionDTO
{
    /** @var PageInfoDTO[]|ArticleInfoDTO[] */
    public array $items;
}
