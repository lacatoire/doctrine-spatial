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
 * MBRDisjoint DQL function tests.
 *
 * @author  Derek J. Lambert <dlambert@dereklambert.com>
 * @author  Alexandre Tranchant <alexandre.tranchant@gmail.com>
 * @license https://dlambert.mit-license.org MIT
 *
 * @group dql
 * @group mariadb-only
 *
 * @internal
 *
 * @coversDefaultClass
 */
class SpMbrDisjointTest extends PersistOrmTestCase
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
     * Test a DQL containing function to test in the predicate.
     *
     * @group geometry
     */
    public function testMbrDisjointWhereParameter(): void
    {
        $bigPolygon = $this->persistBigPolygon();
        $smallPolygon = $this->persistSmallPolygon();
        $outerPolygon = $this->persistOuterPolygon();
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $query = $this->getEntityManager()->createQuery(
            'SELECT p FROM LongitudeOne\Spatial\Tests\Fixtures\PolygonEntity p WHERE MariaDB_MBRDisjoint(p.polygon, ST_GeomFromText(:p)) = 1'
        );

        $query->setParameter('p', 'POLYGON((5 5,7 5,7 7,5 7,5 5))', 'string');

        $result = $query->getResult();

        static::assertIsArray($result);
        static::assertCount(1, $result);
        static::assertEquals($outerPolygon, $result[0]);
        $this->getEntityManager()->clear();

        $query = $this->getEntityManager()->createQuery(
            'SELECT p FROM LongitudeOne\Spatial\Tests\Fixtures\PolygonEntity p WHERE MariaDB_MBRDisjoint(p.polygon, ST_GeomFromText(:p)) = 1'
        );

        $query->setParameter('p', 'POLYGON((15 15,17 15,17 17,15 17,15 15))', 'string');

        $result = $query->getResult();

        static::assertIsArray($result);
        static::assertCount(2, $result);
        static::assertEquals($bigPolygon, $result[0]);
        static::assertEquals($smallPolygon, $result[1]);
    }

    /**
     * Test a DQL containing function to test in the predicate.
     *
     * @group geometry
     */
    public function testSelectMbrDisjoint(): void
    {
        $bigPolygon = $this->persistBigPolygon();
        $smallPolygon = $this->persistSmallPolygon();
        $outerPolygon = $this->persistOuterPolygon();
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $query = $this->getEntityManager()->createQuery(
            'SELECT p, MariaDB_MBRDisjoint(p.polygon, ST_GeomFromText(:p1)) FROM LongitudeOne\Spatial\Tests\Fixtures\PolygonEntity p'
        );

        $query->setParameter('p1', 'POLYGON((5 5,5 7,7 7,7 5,5 5))', 'string');

        $result = $query->getResult();

        static::assertIsArray($result);
        static::assertCount(3, $result);
        static::assertEquals($bigPolygon, $result[0][0]);
        static::assertEquals(0, $result[0][1]);
        static::assertEquals($smallPolygon, $result[1][0]);
        static::assertEquals(0, $result[1][1]);
        static::assertEquals($outerPolygon, $result[2][0]);
        static::assertEquals(1, $result[2][1]);
    }
}
