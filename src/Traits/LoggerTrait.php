<?php

namespace PilipiliWeb\Module\Prestashop\Traits;

use PrestaShop\PrestaShop\Adapter\ServiceLocator;

trait LoggerTrait
{
    /**
     * @var LegacyLogger
     */
    protected $logger;

    /**
     * Return the current logger instance
     *
     * @return Db
     */
    public function getLogger()
    {
        if ($this->logger === null) {
            $this->logger = ServiceLocator::get('\\PrestaShop\\PrestaShop\\Adapter\\LegacyLogger');
        }
        return $this->logger;
    }
}
