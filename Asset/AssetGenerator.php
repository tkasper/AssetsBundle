<?php

namespace Becklyn\AssetsBundle\Asset;

use Becklyn\AssetsBundle\Exception\AssetsException;
use Becklyn\AssetsBundle\Processor\ProcessorRegistry;
use Symfony\Component\Filesystem\Filesystem;


/**
 * Generates the asset instances
 */
class AssetGenerator
{
    /**
     * @var ProcessorRegistry
     */
    private $processorRegistry;


    /**
     * @var string
     */
    private $publicPath;


    /**
     * @var string
     */
    private $outputDir;


    /**
     * @var Filesystem
     */
    private $filesystem;


    /**
     * @param ProcessorRegistry $processorRegistry
     * @param string            $publicPath the absolute path to the public/ (or web/) directory
     * @param string            $outputDir  the output dir relative to the public/ directory
     */
    public function __construct (ProcessorRegistry $processorRegistry, string $publicPath, string $outputDir)
    {
        $this->processorRegistry = $processorRegistry;
        $this->publicPath = rtrim($publicPath, "/");
        $this->outputDir = trim($outputDir, "/");
        $this->filesystem = new Filesystem();
    }


    /**
     * @param string $assetPath
     * @return Asset
     * @throws AssetsException
     */
    public function generateAsset (string $assetPath) : Asset
    {
        $filePath = "{$this->publicPath}/" . ltrim($assetPath, "/");

        if (!\is_file($filePath))
        {
            throw new AssetsException(sprintf(
                "Missing assets file: %s",
                $assetPath
            ));
        }

        $processor = $this->processorRegistry->get($assetPath);
        $fileContent = \file_get_contents($filePath);

        if (null !== $processor)
        {
            $fileContent = $processor->process($assetPath, $fileContent);
        }

        $hash = \base64_encode(\hash("sha256", $fileContent, true));
        $asset = new Asset($this->getOutputDirectory($assetPath), $filePath, $hash);

        $outputPath = "{$this->publicPath}/{$asset->getOutputFilePath()}";

        // ensure that the target directory exists
        $this->filesystem->mkdir(dirname($outputPath));

        // copy file
        $this->filesystem->dumpFile($outputPath, $fileContent);

        return $asset;
    }


    /**
     * Generates the output directory
     *
     * @param string $assetPath
     * @return string
     */
    private function getOutputDirectory (string $assetPath) : string
    {
        $assetPath = ltrim($assetPath, "/");
        $assetPath = dirname($assetPath);

        if ("bundles/" === \substr($assetPath, 0, 8))
        {
            $assetPath = \substr($assetPath, 8);
        }

        return "{$this->outputDir}/{$assetPath}";
    }


    /**
     * Removes all generated files
     */
    public function removeAllGeneratedFiles () : void
    {
        $this->filesystem->remove("{$this->publicPath}/{$this->outputDir}");
    }
}