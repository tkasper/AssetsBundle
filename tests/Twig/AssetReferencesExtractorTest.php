<?php

namespace Becklyn\AssetsBundle\tests\Twig;

use Becklyn\AssetsBundle\Data\AssetReference;
use Becklyn\AssetsBundle\Path\PathGenerator;
use Becklyn\AssetsBundle\tests\BaseTest;
use Becklyn\AssetsBundle\Twig\AssetReferencesExtractor;
use Becklyn\AssetsBundle\Twig\Extension\AssetsTwigExtension;


class AssetReferencesExtractorTest extends BaseTest
{
    private $fixturesDir;
    private $twig;


    public function setUp ()
    {
        $this->fixturesDir = $this->getFixturesDirectory("templates");

        $this->twig = new \Twig_Environment(new \Twig_Loader_Filesystem($this->fixturesDir), [
            "cache" => false,
        ]);

        $pathGenerator = self::getMockBuilder(PathGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->twig->addExtension(new AssetsTwigExtension($pathGenerator));
    }


    /**
     * @group asset-reference-extractor
     * @group asset-reference
     * @group twig
     */
    public function testExtraction ()
    {
        $extractor = new AssetReferencesExtractor($this->twig);
        $assets = $extractor->extractAssetsFromFile($this->fixturesDir . "/example.html.twig");

        self::assertCount(1, $assets);

        $asset = $assets[0];
        self::assertSame("a.css", $asset->getReference());
        self::assertSame(AssetReference::TYPE_STYLESHEET, $asset->getType());
    }


    /**
     * @group asset-reference-extractor
     * @group asset-reference
     * @group twig
     */
    public function testInheritance ()
    {
        $extractor = new AssetReferencesExtractor($this->twig);
        $assets = $extractor->extractAssetsFromFile("{$this->fixturesDir}/inheritance/inheritance.html.twig");

        self::assertCount(1, $assets);

        $asset = $assets[0];
        self::assertSame("b.css", $asset->getReference());
        self::assertSame(AssetReference::TYPE_STYLESHEET, $asset->getType());
    }
}