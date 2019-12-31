<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mail;

use Laminas\Validator\EmailAddress as EmailAddressValidator;
use Laminas\Validator\Hostname;

class Address implements Address\AddressInterface
{
    protected $email;
    protected $name;

    /**
     * Constructor
     *
     * @param  string $email
     * @param  null|string $name
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($email, $name = null)
    {
        $emailAddressValidator = new EmailAddressValidator(Hostname::ALLOW_DNS | Hostname::ALLOW_LOCAL);
        if (! is_string($email) || empty($email)) {
            throw new Exception\InvalidArgumentException('Email must be a valid email address');
        }

        if (preg_match("/[\r\n]/", $email)) {
            throw new Exception\InvalidArgumentException('CRLF injection detected');
        }

        if (! $emailAddressValidator->isValid($email)) {
            $invalidMessages = $emailAddressValidator->getMessages();
            throw new Exception\InvalidArgumentException(array_shift($invalidMessages));
        }

        if (null !== $name) {
            if (! is_string($name)) {
                throw new Exception\InvalidArgumentException('Name must be a string');
            }

            if (preg_match("/[\r\n]/", $name)) {
                throw new Exception\InvalidArgumentException('CRLF injection detected');
            }

            $this->name = $name;
        }

        $this->email = $email;
    }

    /**
     * Retrieve email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Retrieve name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * String representation of address
     *
     * @return string
     */
    public function toString()
    {
        $string = '<' . $this->getEmail() . '>';
        $name   = $this->getName();
        if (null === $name) {
            return $string;
        }

        return $name . ' ' . $string;
    }
}
