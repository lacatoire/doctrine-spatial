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

namespace LongitudeOne\Spatial\Tests\ORM\Query\AST\Functions\MariaDB;

use Doctrine\DBAL\Platforms\MariaDBPlatform;
use LongitudeOne\Spatial\Tests\Helper\PersistantPolygonHelperTrait;
use LongitudeOne\Spatial\Tests\PersistOrmTestCase;

/**
 * MariaDB_MbrOverlaps DQL function tests.
 *
 * @author  Alexandre Tranchant <alexandre.tranchant@gmail.com>
 * @license https://alexandre-tranchant.mit-license.org MIT
 *
 * @group dql
 * @group mariadb-only
 *
 * @internal
 *
 * @coversDefaultClass
 */
class SpMbrOverlapsTest extends PersistOrmTestCase
{
    use PersistantPolygonHelperTrait;

    /**
     * Set up the function type test.
     */
    protected function setUp(): void
    {
        $this->usesEntity(self::POLYGON_ENTITY);
        $this->supportsPlatform(MariaDBPlatform::class);

        parent::setUp();
    }

    /**
     * Test a DQL containing function to test in the select.
     *
     * @group geometry
     */
    public function testFunctionInPredicate(): void
    {
        $bigPolygon = $this->persistBigPolygon();
        $this->persistSmallPolygon();
        $this->persistHoleyPolygon();
        $polygonW = $this->persistPolygonW();
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $query = $this->getEntityManager()->createQuery(
            'SELECT p FROM LongitudeOne\Spatial\Tests\Fixtures\PolygonEntity p WHERE MariaDB_MbrOverlaps(p.polygon, ST_GeomFromText(:p)) = true'
        );
        $query->setParameter('p', 'POLYGON((4 4, 4 12, 12 12, 12 4, 4 4))', 'string');
        $result = $query->getResult();

        static::assertIsArray($result);
        static::assertCount(3, $result);
        static::assertEquals($bigPolygon, $result[0]);
        static::assertEquals($polygonW, $result[2]);
    }

    /**
     * Test a DQL containing function to test.
     *
     * @group geometry
     */
    public function testFunctionInSelect(): void
    {
        $bigPolyon = $this->persistBigPolygon();
        $smallPolygon = $this->persistSmallPolygon();
        $polygonW = $this->persistPolygonW();
        $holeyPolygon = $this->persistHoleyPolygon();
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $query = $this->getEntityManager()->createQuery(
            'SELECT p, MariaDB_MbrOverlaps(p.polygon, ST_GeomFromText(:p)) FROM LongitudeOne\Spatial\Tests\Fixtures\PolygonEntity p'
        );
        $query->setParameter('p', 'POLYGON((0 0, 0 12, 12 12, 12 0, 0 0))', 'string');
        $result = $query->getResult();

        static::assertIsArray($result);
        static::assertCount(4, $result);
        static::assertEquals($bigPolyon, $result[0][0]);
        static::assertEquals(0, $result[0][1]);
        static::assertEquals($smallPolygon, $result[1][0]);
        static::assertEquals(0, $result[1][1]);
        static::assertEquals($polygonW, $result[2][0]);
        static::assertEquals(1, $result[2][1]);
        static::assertEquals($holeyPolygon, $result[3][0]);
        static::assertEquals(0, $result[3][1]);
    }
}
