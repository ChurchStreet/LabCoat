<?php

/*
 * This file is part of the ChurchStreet LabCoat package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChurchStreet\LabCoat\Model;

/**
 * Milestone
 *
 * Represents a GitLab milestone
 *
 * @author Tom Haskins-Vaughan <tom@tomhv.uk>
 * @since  0.1.0
 */
class Milestone extends Resource
{
    /**
     * issues
     *
     * @var array|Issue[]
     */
    public $issues = [];

    /**
     * estimatesHours
     *
     * @var float
     */
    public $estimatedHours = 0;

    /**
     * actualHours
     *
     * @var float
     */
    public $actualHours = 0;

    /**
     * Get default metaData
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @return array
     */
    public function getDefaultMetaData()
    {
        return [];
    }
}
