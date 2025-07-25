<?php
/**
 * This file is part of the doctrine spatial extension.
 *
 * PHP 8.1 | 8.2 | 8.3
 * Doctrine ORM 2.19 | 3.1
 *
 * Copyright Alexandre Tranchant <alexandre.tranchant@gmail.com> 2017-2025
 * Copyright Longitude One 2020-2025
 * Copyright 2015 Derek J. Lambert
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

declare(strict_types=1);

namespace LongitudeOne\Spatial\Tests\ORM\Query;

use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use LongitudeOne\Spatial\Tests\Helper\PersistantLineStringHelperTrait;
use LongitudeOne\Spatial\Tests\Helper\PersistantPointHelperTrait;
use LongitudeOne\Spatial\Tests\PersistOrmTestCase;

/**
 * GeometryWalker tests.
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @author  Alexandre Tranchant <alexandre.tranchant@gmail.com>
 * @license https://dlambert.mit-license.org MIT
 *
 * @group dql
 *
 * @internal
 *
 * @coversDefaultClass
 */
class GeometryWalkerTest extends PersistOrmTestCase
{
    use PersistantLineStringHelperTrait;
    use PersistantPointHelperTrait;

    /**
     * Set up the function type test.
     */
    protected function setUp(): void
    {
        $this->usesType('geometry');
        $this->usesEntity(self::LINESTRING_ENTITY);
        $this->supportsPlatform(PostgreSQLPlatform::class);
        $this->supportsPlatform(MariaDBPlatform::class);
        $this->supportsPlatform(MySQLPlatform::class);
        parent::setUp();
    }

    /**
     * Start the test.
     *
     * @param EntityManagerInterface $entityManager Entity manager that persists data
     * @param string                 $convert       convert function name (ST_AsBinary, ST_AsText)
     * @param string                 $startPoint    start point function name (ST_StartPoint)
     * @param string                 $envelope      envelope function name (ST_Envelop)
     */
    private static function test(
        EntityManagerInterface $entityManager,
        string $convert,
        string $startPoint,
        string $envelope
    ): void {
        $queryString = sprintf(
            'SELECT %s(%s(l.lineString)) FROM LongitudeOne\Spatial\Tests\Fixtures\LineStringEntity l',
            $convert,
            $startPoint
        );
        $query = $entityManager->createQuery($queryString);
        $query->setHint(
            Query::HINT_CUSTOM_OUTPUT_WALKER,
            'LongitudeOne\Spatial\ORM\Query\GeometryWalker'
        );

        $result = $query->getResult();
        static::assertIsArray($result);
        static::assertEquals(static::createPointOrigin(), $result[0][1]);
        static::assertEquals(static::createPointC(), $result[1][1]);

        $queryString = sprintf(
            'SELECT %s(%s(l.lineString)) FROM LongitudeOne\Spatial\Tests\Fixtures\LineStringEntity l',
            $convert,
            $envelope
        );
        $query = $entityManager->createQuery($queryString);
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, 'LongitudeOne\Spatial\ORM\Query\GeometryWalker');

        $result = $query->getResult();
        static::assertIsArray($result);
        static::assertInstanceOf('LongitudeOne\Spatial\PHP\Types\Geometry\Polygon', $result[0][1]);
        static::assertInstanceOf('LongitudeOne\Spatial\PHP\Types\Geometry\Polygon', $result[1][1]);
    }

    /**
     * Test the geometry walker binary.
     *
     * @group geometry
     */
    public function testGeometryWalkerBinary(): void
    {
        $this->persistStraightLineString();
        $this->persistAngularLineString();

        self::test($this->getEntityManager(), 'ST_AsBinary', 'ST_StartPoint', 'ST_Envelope');
    }

    /**
     * Test the geometry walker.
     *
     * @group geometry
     */
    public function testGeometryWalkerText(): void
    {
        $this->persistStraightLineString();
        $this->persistAngularLineString();

        self::test($this->getEntityManager(), 'ST_AsText', 'ST_StartPoint', 'ST_Envelope');
    }
}
