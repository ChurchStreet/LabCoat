<?php

/*
 * This file is part of the ChurchStreet LabCoat package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChurchStreet\LabCoat\Model;

/**
 * Issue
 *
 * Represents a GitLab issue
 *
 * @author Tom Haskins-Vaughan <tom@tomhv.uk>
 * @since  0.1.0
 */
class Issue extends Resource
{
    /**
     * Priority
     *
     * @var string
     */
    const
        PRIORITY_LOW = 'Low',
        PRIORITY_MEDIUM = 'Medium',
        PRIORITY_HIGH = 'High'
    ;

    /**
     * Status
     *
     * @var string
     */
    const
        STATUS_QUEUED = 'Queued',
        STATUS_DEV = 'In development',
        STATUS_UAT = 'UAT',
        STATUS_FAILED_UAT = 'Failed UAT',
        STATUS_READY_TO_SHIP = 'Ready to ship'
    ;

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
        return [
            'estimated' => 0,
            'actual' => 0,
            'priority' => Issue::PRIORITY_MEDIUM,
            'status' => Issue::STATUS_QUEUED,
        ];
    }
}
