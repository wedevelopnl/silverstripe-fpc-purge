<?php

declare(strict_types=1);

namespace WeDevelop\FPCPurge\Extensions;

use SilverStripe\Core\Extension;

final class FPCPurgeControllerExtension extends Extension
{
    public function onBeforeInit(): void
    {
        if (!method_exists($this->getOwner(), 'updateCacheControl')) {
            throw new \LogicException('Missing method updateCacheControl on class ' . get_class($this->getOwner()));
        }

        $this->getOwner()->updateCacheControl();
    }
}
