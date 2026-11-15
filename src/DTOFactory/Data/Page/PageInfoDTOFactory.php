<?php

declare(strict_types=1);

namespace Vtsot\CmsAdminClientBundle\DTOFactory\Data\Page;

use Vtsot\CmsAdminClientBundle\Contract\Dictionary\PageType;
use Vtsot\CmsAdminClientBundle\DTO\Data\Page\PageInfoDTO;

readonly class PageInfoDTOFactory
{
    public function fromAPICollection(array $collection): array
    {
        $result = [];

        foreach ($collection as $item) {
            $result[] = $this->fromAPI($item);
        }

        return $result;
    }

    public function fromAPI(array $data): PageInfoDTO
    {
        return new PageInfoDTO(
            id: $data['id'] ?? '',
            slug: $data['slug'] ?? '',
            type: PageType::Page,
            isHomepage: $data['is_homepage'] ?? false,
            title: $data['title'] ?? null,
            content: $data['content'] ?? null,
            sources: $data['sources'] ?? null,
        );
    }
}
