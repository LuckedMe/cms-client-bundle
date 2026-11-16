<?php

declare(strict_types=1);

namespace LuckedMe\CmsClientBundle\DTO\Collection;

use LuckedMe\CmsClientBundle\DTO\Data\Page\ArticleInfoDTO;
use LuckedMe\CmsClientBundle\DTO\Data\Page\PageInfoDTO;

final readonly class PageInfoCollectionDTO extends AbstractCollectionDTO
{
    /** @var PageInfoDTO[]|ArticleInfoDTO[] */
    public array $items;
}
