<?php

declare(strict_types=1);

namespace WeDevelop\FPCPurge\Extensions;

use SilverStripe\ORM\DataExtension;
use WeDevelop\FPCPurge\FPCPurgeService;

final class FPCPurgeExtension extends DataExtension
{
    public function onAfterPublish(): void
    {
        FPCPurgeService::purge();
    }
}
