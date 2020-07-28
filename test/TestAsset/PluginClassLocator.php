<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Loader;

/**
 * @todo Remove this file for 3.0.0
 */
if (! class_exists(PluginClassLocator::class)
    && ! interface_exists(PluginClassLocator::class)
) {
    class PluginClassLocator
    {
    }
}
