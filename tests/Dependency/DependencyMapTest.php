<?php declare(strict_types=1);

namespace Becklyn\AssetsBundle\tests\Dependency;

use Becklyn\AssetsBundle\Dependency\DependencyMap;
use PHPUnit\Framework\TestCase;

class DependencyMapTest extends TestCase
{
    /**
     * @var DependencyMap
     */
    private $map;


    /**
     * @inheritDoc
     */
    protected function setUp () : void
    {
        $this->map = new DependencyMap([
            "a" => [1, 2, "a-dep"],
            "b" => [1, "b-dep"],
        ]);
    }


    /**
     * Tests the correct order and imports.
     */
    public function testUniqueAndCorrectOrder () : void
    {
        $load = $this->map->getImportsWithDependencies(["a", "b"]);
        self::assertSame([1, 2, "a-dep", "b-dep"], $load);
    }


    /**
     * Test loading with missing dependency map entry.
     */
    public function testMissing () : void
    {
        $load = $this->map->getImportsWithDependencies(["a", "c", "b"]);
        self::assertSame([1, 2, "a-dep", "c", "b-dep"], $load);
    }
}
