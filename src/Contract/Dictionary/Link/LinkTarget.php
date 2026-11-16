<?php

declare(strict_types=1);

namespace LuckedMe\CmsClientBundle\Contract\Dictionary\Link;

enum LinkTarget: string
{
    case Top   = '_top';
    case Blank = '_blank';
}
