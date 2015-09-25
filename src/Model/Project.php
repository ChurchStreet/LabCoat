<?php

/*
 * This file is part of the ChurchStreet LabCoat package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChurchStreet\LabCoat\Model;

/**
 * Project
 *
 * Represents a GitLab project
 *
 * @author Tom Haskins-Vaughan <tom@tomhv.uk>
 * @since  0.1.0
 */
class Project extends Resource
{
    /**
     * issues
     *
     * @var array|Issue[]
     */
    public $issues = [];

    /**
     * openEstimatedHours
     *
     * @var float
     */
    public $openEstimatedHours = 0;

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
