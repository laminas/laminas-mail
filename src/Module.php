<?php

declare(strict_types=1);

namespace Laminas\Mail;

class Module
{
    /**
     * Retrieve laminas-mail package configuration for laminas-mvc context.
     *
     * @return array
     */
    public function getConfig()
    {
        $provider = new ConfigProvider();
        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }
}
