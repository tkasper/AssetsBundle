<?php

namespace Becklyn\AssetsBundle\Html;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\Asset\AssetsRegistry;
use Becklyn\AssetsBundle\Dependency\DependencyMap;
use Becklyn\AssetsBundle\Dependency\DependencyMapFactory;
use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\File\FileTypeRegistry;
use Becklyn\AssetsBundle\Url\AssetUrl;


class AssetHtmlGenerator
{
    /**
     * @var AssetsRegistry
     */
    private $registry;


    /**
     * @var AssetUrl
     */
    private $assetUrl;


    /**
     * @var FileTypeRegistry
     */
    private $fileTypeRegistry;


    /**
     * @var bool
     */
    private $isDebug;


    /**
     * @var DependencyMap
     */
    private $dependencyMap;


    /**
     *
     * @param AssetsRegistry       $registry
     * @param AssetUrl             $assetUrl
     * @param FileTypeRegistry     $fileTypeRegistry
     * @param bool                 $isDebug
     * @param DependencyMapFactory $dependencyMapFactory
     */
    public function __construct (
        AssetsRegistry $registry,
        AssetUrl $assetUrl,
        FileTypeRegistry $fileTypeRegistry,
        bool $isDebug,
        DependencyMapFactory $dependencyMapFactory
    )
    {
        $this->registry = $registry;
        $this->assetUrl = $assetUrl;
        $this->fileTypeRegistry = $fileTypeRegistry;
        $this->isDebug = $isDebug;
        $this->dependencyMap = $dependencyMapFactory->getDependencyMap();
    }


    /**
     *
     * @param string[] $assetPaths
     *
     * @throws AssetsException
     */
    public function linkAssets (array $assetPaths, bool $withDependencies = true) : string
    {
        $html = "";

        if ($withDependencies)
        {
            $assetPaths = $this->dependencyMap->getImportsWithDependencies($assetPaths);
        }

        foreach ($assetPaths as $assetPath)
        {
            // allow URLs with integrated optional integrity.
            // just pass it behind a hash:
            // https://example.org/test.js#sha256hash
            if (1 === \preg_match('~^(https?:)?//~', $assetPath))
            {
                $parts = explode("#", $assetPath, 2);
                $assetUrl = $parts[0];
                \parse_str($parts[1] ?? "", $urlParameters);
                $integrity = $urlParameters["integrity"] ?? "";
                $extension = $urlParameters["type"] ?? \pathinfo($assetUrl, \PATHINFO_EXTENSION);
                $fileType = $this->fileTypeRegistry->getByFileExtension($extension);
            }
            else
            {
                $asset = Asset::createFromAssetPath($assetPath);
                $assetUrl = $this->assetUrl->generateUrl($asset);
                $fileType = $this->fileTypeRegistry->getFileType($asset);
                $extension = $asset->getFileType();
                $integrity = $this->isDebug ? "" : $this->getIntegrityHtml($asset);
            }

            $htmlLinkFormat = $fileType->getHtmlLinkFormat();

            if (null === $htmlLinkFormat)
            {
                throw new AssetsException(sprintf(
                    "No HTML link format found for file of type: %s",
                    $extension
                ));
            }

            $html .= sprintf($htmlLinkFormat, $assetUrl, $integrity);
        }

        return $html;
    }


    /**
     * Returns the integrity HTML snippet
     *
     * @param Asset $asset
     * @return string
     * @throws AssetsException
     */
    private function getIntegrityHtml (Asset $asset) : string
    {
        return $this->isDebug
            ? ""
            : sprintf(
                ' integrity="sha256-%s"',
                $this->registry->get($asset)->getHash()
            );
    }
}
