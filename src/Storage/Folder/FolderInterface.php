<?php

declare(strict_types=1);

namespace Laminas\Mail\Storage\Folder;

use Laminas\Mail\Storage\Exception\ExceptionInterface;

interface FolderInterface
{
    /**
     * get root folder or given folder
     *
     * @param string $rootFolder get folder structure for given folder, else root
     * @return FolderInterface root or wanted folder
     */
    public function getFolders($rootFolder = null);

    /**
     * select given folder
     *
     * folder must be selectable!
     *
     * @param FolderInterface|string $globalName global name of folder or instance for subfolder
     * @throws ExceptionInterface
     */
    public function selectFolder($globalName);

    /**
     * get Laminas\Mail\Storage\Folder instance for current folder
     *
     * @return FolderInterface instance of current folder
     * @throws ExceptionInterface
     */
    public function getCurrentFolder();
}
