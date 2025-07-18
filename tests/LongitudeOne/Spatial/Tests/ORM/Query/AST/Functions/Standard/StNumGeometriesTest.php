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

namespace LongitudeOne\Spatial\Tests\ORM\Query\AST\Functions\Standard;

use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use LongitudeOne\Spatial\Tests\Helper\PersistantMultiPointHelperTrait;
use LongitudeOne\Spatial\Tests\PersistOrmTestCase;

/**
 * ST_NumGeometries DQL function tests.
 *
 * @author  Alexandre Tranchant <alexandre-tranchant@gmail.com>
 * @license https://alexandre-tranchant.mit-license.org MIT
 *
 * @group dql
 *
 * @internal
 *
 * @coversDefaultClass
 */
class StNumGeometriesTest extends PersistOrmTestCase
{
    use PersistantMultiPointHelperTrait;

    /**
     * Set up the function type test.
     */
    protected function setUp(): void
    {
        $this->usesEntity(self::MULTIPOINT_ENTITY);
        $this->supportsPlatform(PostgreSQLPlatform::class);
        $this->supportsPlatform(MariaDBPlatform::class);
        $this->supportsPlatform(MySQLPlatform::class);

        parent::setUp();
    }

    /**
     * Test a DQL containing function to test in the select.
     *
     * @group geometry
     */
    public function testSelectStNumGeometries(): void
    {
        $four = $this->persistFourPoints();
        $single = $this->persistSinglePoint();

        $query = $this->getEntityManager()->createQuery(
            'SELECT m, ST_NumGeometries(m.multiPoint) FROM LongitudeOne\Spatial\Tests\Fixtures\MultiPointEntity m'
        );
        $result = $query->getResult();

        static::assertIsArray($result);
        static::assertCount(2, $result);
        static::assertEquals($four, $result[0][0]);
        static::assertEquals(4, $result[0][1]);
        static::assertEquals($single, $result[1][0]);
        static::assertEquals(1, $result[1][1]);
    }

    /**
     * Test a DQL containing function to test in the predicate.
     *
     * @group geometry
     */
    public function testStNumGeometriesInPredicate(): void
    {
        $this->persistFourPoints();
        $single = $this->persistSinglePoint();

        $query = $this->getEntityManager()->createQuery(
            'SELECT m FROM LongitudeOne\Spatial\Tests\Fixtures\MultiPointEntity m WHERE ST_NumGeometries(m.multiPoint) = :p'
        );
        $query->setParameter('p', 1);
        $result = $query->getResult();

        static::assertIsArray($result);
        static::assertCount(1, $result);
        static::assertEquals($single, $result[0]);
    }
}
