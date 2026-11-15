<?php

declare(strict_types=1);

namespace Vtsot\CmsAdminClientBundle\DTO\Data\Page;

use Vtsot\CmsAdminClientBundle\Contract\Dictionary\PageSitemapChangeFrequency;
use Vtsot\CmsAdminClientBundle\Contract\Dictionary\PageSitemapPriority;
use Vtsot\CmsAdminClientBundle\Contract\Dictionary\PageType;
use Vtsot\CmsAdminClientBundle\DTOFactory\Data\Page\PageDetailsDTOFactory;

/**
 * @see PageDetailsDTOFactory
 *
 * @property ArticleInfoDTO[] $relatedArticles
 */
readonly class PageDetailsDTO
{
    public function __construct(
        public string $id,
        public string $slug,
        public PageType $type,
        public ?bool $isHomepage,
        public ?string $title,
        public ?string $content,
        public ?string $relatedArticlesTitle,
        public ?array $relatedArticles = [],
        public ?string $sources,
        public ?string $redirect,
        public ?string $codeBlock,
        public ?string $metaTitle,
        public ?string $metaKeywords,
        public ?string $metaDescription,
        public ?string $ogTitle,
        public ?string $ogDescription,
        public ?string $ogImage,
        public ?bool $indexed,
        public ?PageSitemapChangeFrequency $sitemapChangeFrequency = PageSitemapChangeFrequency::DEFAULT,
        public ?PageSitemapPriority $sitemapPriority = PageSitemapPriority::DEFAULT,
    ) {
    }
}
