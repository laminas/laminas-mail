<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mail\Protocol;

use Laminas\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager implementation for SMTP extensions.
 *
 * Enforces that SMTP extensions retrieved are instances of Smtp. Additionally,
 * it registers a number of default extensions available.
 */
class SmtpPluginManager extends AbstractPluginManager
{
    /**
     * Default set of extensions
     *
     * @var array
     */
    protected $invokableClasses = [
        'crammd5' => 'Laminas\Mail\Protocol\Smtp\Auth\Crammd5',
        'login'   => 'Laminas\Mail\Protocol\Smtp\Auth\Login',
        'plain'   => 'Laminas\Mail\Protocol\Smtp\Auth\Plain',
        'smtp'    => 'Laminas\Mail\Protocol\Smtp',
    ];

    /**
     * Validate the plugin
     *
     * Checks that the extension loaded is an instance of Smtp.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\InvalidArgumentException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Smtp) {
            // we're okay
            return;
        }

        throw new Exception\InvalidArgumentException(sprintf(
            'Plugin of type %s is invalid; must extend %s\Smtp',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
