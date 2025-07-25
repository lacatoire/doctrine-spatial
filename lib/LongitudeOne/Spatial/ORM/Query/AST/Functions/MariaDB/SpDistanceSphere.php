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

namespace LongitudeOne\Spatial\ORM\Query\AST\Functions\MariaDB;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\MariaDBPlatform;
use LongitudeOne\Spatial\ORM\Query\AST\Functions\AbstractSpatialDQLFunction;

/**
 * ST_Distance_Sphere DQL function.
 *
 * Be careful, this function is not described in the ISO/IEC 13249.
 * So this class is not in the Standard directory.
 * With MariaDB, its name's ST_Distance_Sphere.
 * With PostGreSQL, its name's ST_DistanceSphere since PostGis 2.1.
 * So these two functions cannot be merged in a class stored in the Common directory.
 *
 * @author  Alexandre Tranchant <alexandre.tranchant@gmail.com>
 */
class SpDistanceSphere extends AbstractSpatialDQLFunction
{
    /**
     * Function SQL name getter.
     */
    protected function getFunctionName(): string
    {
        return 'ST_Distance_Sphere';
    }

    /**
     * Maximum number of parameter for the spatial function.
     *
     * @return int the inherited methods shall NOT return null, but 0 when function has no parameter
     */
    protected function getMaxParameter(): int
    {
        return 2;
    }

    /**
     * Minimum number of parameter for the spatial function.
     *
     * @since 2.0 This function replace the protected property minGeomExpr.
     *
     * @return int the inherited methods shall NOT return null, but 0 when function has no parameter
     */
    protected function getMinParameter(): int
    {
        return 2;
    }

    /**
     * Get the platforms accepted.
     *
     * @since 2.0 This function replace the protected property platforms.
     * @since 5.0 This function returns the class-string[] instead of string[]
     *
     * @return class-string<AbstractPlatform>[] a non-empty array of accepted platforms
     */
    protected function getPlatforms(): array
    {
        return [MariaDBPlatform::class];
    }
}
