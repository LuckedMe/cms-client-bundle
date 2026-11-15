<?php

declare(strict_types=1);

namespace Vtsot\CmsAdminClientBundle\Contract\Dictionary;

use Vtsot\CmsAdminClientBundle\Contract\Dictionary\Traits\EnumValuesTrait;

enum PageType: string
{
    use EnumValuesTrait;

    case Page    = 'page';
    case Article = 'article';
}
