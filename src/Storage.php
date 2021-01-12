<?php

/**
 * @see       https://github.com/laminas/laminas-mail for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mail/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mail/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mail;

class Storage
{
    // maildir and IMAP flags, using IMAP names, where possible to be able to distinguish between IMAP
    // system flags and other flags
    public const FLAG_PASSED   = 'Passed';
    public const FLAG_SEEN     = '\Seen';
    public const FLAG_UNSEEN   = '\Unseen';
    public const FLAG_ANSWERED = '\Answered';
    public const FLAG_FLAGGED  = '\Flagged';
    public const FLAG_DELETED  = '\Deleted';
    public const FLAG_DRAFT    = '\Draft';
    public const FLAG_RECENT   = '\Recent';
}
