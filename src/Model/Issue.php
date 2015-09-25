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
        ];
    }
}
