<?php

namespace Becklyn\AssetsBundle\File\Type;

use Becklyn\AssetsBundle\Asset\Asset;


abstract class FileType
{
    /**
     * Adds the file header
     *
     * @param Asset  $asset
     * @param string $filePath
     * @param string $fileContent
     * @return string
     */
    public function prependFileHeader (Asset $asset, string $filePath, string $fileContent) : string
    {
        return $fileContent;
    }


    /**
     * Processes the file content for production
     *
     * @param Asset  $asset
     * @param string $fileContent
     * @return string
     */
    public function processForProd (Asset $asset, string $fileContent) : string
    {
        return $fileContent;
    }


    /**
     * Returns whether the file should be loaded deferred
     *
     * @return bool
     */
    public function importDeferred () : bool
    {
        return false;
    }


    /**
     * Returns the link format to link to this file type from HTML.
     *
     * Is passed to sprintf() with the following parameters:
     *      1: the url
     *      2: integrity HTML attribute
     *
     * @return null|string
     */
    public function getHtmlLinkFormat () : ?string
    {
        return null;
    }
}
