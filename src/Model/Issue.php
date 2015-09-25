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
class Issue
{
    /**
     * apiData
     *
     * @var array
     */
    private $apiData;

    /**
     * metaData
     *
     * @var array
     */
    private $metaData;

    /**
     * __construct()
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @param array $apiData
     */
    public function __construct(array $apiData)
    {
        $this->apiData = $apiData;
        $this->metaData = $this->getMetaData($apiData);
    }

    /**
     * __get()
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @return mixed
     */
    public function __get($key)
    {
        $key = lcfirst($key);

        if (array_key_exists($key, $this->metaData)) {
            return $this->metaData[$key];
        }

        if (array_key_exists($key, $this->apiData)) {
            return $this->apiData[$key];
        }

        throw new \InvalidArgumentException(sprintf(
            'Cannot access %s',
            $key
        ));
    }

    /**
     * __isset()
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @return mixed
     */
    public function __isset($key)
    {
        $key = lcfirst($key);

        if (array_key_exists($key, $this->metaData)) {
            return true;
        }

        if (array_key_exists($key, $this->apiData)) {
            return true;
        }

        false;
    }

    /**
     * Parse apiData for metaData
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @param array
     */
    private function getMetaData($apiData)
    {
        $metaData = [
            'estimated' => 0,
            'actual' => 0,
        ];

        $pattern = '/<!--\DO NOT EDIT OR REMOVE THIS LINE({.*})-->/';

        if (preg_match($pattern, $this->apiData['description'], $matches)) {
            $meta = json_decode($matches[1], true);

            $metaData['estimated'] = $meta['estimated'];
            $metaData['actual'] = $meta['actual'];
        }

        return $metaData;
    }
}
