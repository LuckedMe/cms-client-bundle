<?php

declare(strict_types=1);

namespace LuckedMe\CmsClientBundle\Contract\Dictionary;

use LuckedMe\CmsClientBundle\Contract\Dictionary\Traits\EnumValuesTrait;

enum PageType: string
{
    use EnumValuesTrait;

    case Page    = 'page';
    case Article = 'article';
}
