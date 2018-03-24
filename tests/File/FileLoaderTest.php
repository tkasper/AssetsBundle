<?php

namespace Tests\Becklyn\AssetsBundle\File;

use Becklyn\AssetsBundle\Asset\Asset;
use Becklyn\AssetsBundle\File\FileLoader;
use Becklyn\AssetsBundle\File\FileTypeRegistry;
use Becklyn\AssetsBundle\File\Type\FileType;
use Becklyn\AssetsBundle\File\Type\GenericFile;
use Becklyn\AssetsBundle\Namespaces\NamespaceRegistry;
use PHPUnit\Framework\TestCase;


class FileLoaderTest extends TestCase
{
    /**
     * @var FileLoader
     */
    private $loader;


    /**
     * @var string
     */
    private $fixtures = __DIR__ . "/../fixtures";


    /**
     * @var NamespaceRegistry
     */
    private $namespaceRegistry;


    protected function setUp ()
    {
        $this->namespaceRegistry = new NamespaceRegistry($this->fixtures, [
            "bundles" => "public/bundles"
        ]);

        $fileTypes = new FileTypeRegistry(new GenericFile());

        $this->loader = new FileLoader($this->namespaceRegistry, $fileTypes);
    }


    public function dataProviderValid ()
    {
        return [
            [new Asset("bundles", "test/css/app.css"), "{$this->fixtures}/public/bundles/test/css/app.css"],
            [new Asset("bundles", "test/js/test.js"), "{$this->fixtures}/public/bundles/test/js/test.js"],
        ];
    }


    /**
     * @dataProvider dataProviderValid
     *
     * @param Asset  $asset
     * @param string $expectedFile
     */
    public function testValid (Asset $asset, string $expectedFile)
    {
        self::assertStringEqualsFile($expectedFile, $this->loader->loadFile($asset, FileLoader::MODE_UNTOUCHED));
    }


    public function dataProviderInvalid ()
    {
        return [
            [new Asset("invalid", "test.js")],
            [new Asset("bundles", "test/js/doesnt_exist.js")],
            [new Asset("Invalid", "test.js")],
        ];
    }


    /**
     * @dataProvider dataProviderInvalid
     * @param Asset $asset
     *
     * @expectedException \Becklyn\AssetsBundle\Exception\AssetsException
     */
    public function testInvalid (Asset $asset)
    {
        $this->loader->loadFile($asset, FileLoader::MODE_UNTOUCHED);
    }


    /**
     * Tests, that the custom file type is correctly called in dev
     */
    public function testCustomProcessorCalledInDev ()
    {
        $testFileType = $this->getMockBuilder(FileType::class)
            ->disableOriginalConstructor()
            ->getMock();

        $testFileType
            ->expects(self::once())
            ->method("prependFileHeader")
            ->willReturnArgument(2);

        $fileTypes = new FileTypeRegistry(new GenericFile(), [
            "css" => $testFileType,
        ]);

        $loader = new FileLoader($this->namespaceRegistry, $fileTypes);
        $loader->loadFile(new Asset("bundles", "test/css/app.css"), FileLoader::MODE_DEV);
    }


    /**
     * Tests, that the custom file type is correctly called in prod
     */
    public function testCustomProcessorCalledInProd ()
    {
        $testFileType = $this->getMockBuilder(FileType::class)
            ->disableOriginalConstructor()
            ->getMock();

        $testFileType
            ->expects(self::once())
            ->method("processForProd")
            ->willReturnArgument(1);

        $fileTypes = new FileTypeRegistry(new GenericFile(), [
            "css" => $testFileType,
        ]);

        $loader = new FileLoader($this->namespaceRegistry, $fileTypes);
        $loader->loadFile(new Asset("bundles", "test/css/app.css"), FileLoader::MODE_PROD);
    }


    /**
     * Tests, that the custom file type is correctly called in prod
     */
    public function testCustomProcessorNotCalledInUntouched ()
    {
        $testFileType = $this->getMockBuilder(FileType::class)
            ->disableOriginalConstructor()
            ->getMock();

        $testFileType
            ->expects(self::never())
            ->method("processForProd");

        $fileTypes = new FileTypeRegistry(new GenericFile(), [
            "css" => $testFileType,
        ]);

        $loader = new FileLoader($this->namespaceRegistry, $fileTypes);
        $loader->loadFile(new Asset("bundles", "test/css/app.css"), FileLoader::MODE_UNTOUCHED);
    }


    /**
     * Tests, that the fallback type is correctly called
     */
    public function testFallbackType ()
    {
        $testFileType = $this->getMockBuilder(FileType::class)
            ->disableOriginalConstructor()
            ->getMock();

        $testFileType
            ->expects(self::never())
            ->method("processForProd");

        $genericFileType = $this->getMockBuilder(FileType::class)
            ->disableOriginalConstructor()
            ->getMock();

        $genericFileType
            ->expects(self::once())
            ->method("processForProd")
            ->willReturnArgument(1);

        $fileTypes = new FileTypeRegistry($genericFileType, [
            "css" => $testFileType,
        ]);

        $loader = new FileLoader($this->namespaceRegistry, $fileTypes);
        $loader->loadFile(new Asset("bundles", "test/js/test.js"), FileLoader::MODE_PROD);
    }
}