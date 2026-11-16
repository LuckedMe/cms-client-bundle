<?php

declare(strict_types=1);

namespace LuckedMe\CmsClientBundle\Contract\Dictionary;

use LuckedMe\CmsClientBundle\Contract\Dictionary\Traits\EnumValuesTrait;

enum PageSitemapChangeFrequency: string
{
    use EnumValuesTrait;

    public const PageSitemapChangeFrequency DEFAULT = self::Weekly;

    case Always  = 'always';
    case Hourly  = 'hourly';
    case Daily   = 'daily';
    case Weekly  = 'weekly';
    case Monthly = 'monthly';
    case Yearly  = 'yearly';
    case Never   = 'never';
}
