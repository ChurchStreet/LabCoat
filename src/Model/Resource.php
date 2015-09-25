<?php

/*
 * This file is part of the ChurchStreet LabCoat package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChurchStreet\LabCoat\Model;

/**
 * Resource
 *
 * Represents a GitLab resource
 *
 * @author Tom Haskins-Vaughan <tom@tomhv.uk>
 * @since  0.1.0
 */
abstract class Resource
{
    /**
     * apiData
     *
     * @var array
     */
    protected $apiData;

    /**
     * metaData
     *
     * @var array
     */
    protected $metaData;

    /**
     * __construct()
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @param array $apiData
     */
    public function __construct(array $apiData, array $metaData)
    {
        $this->apiData = $apiData;
        $this->metaData = array_merge($this->getDefaultMetaData(), $metaData);
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
        if (array_key_exists($key, $this->metaData)) {
            return true;
        }

        if (array_key_exists($key, $this->apiData)) {
            return true;
        }

        false;
    }

    /**
     * Get metaData
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0 (2015-09-24)
     *
     * @return array
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * Get default metaData
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @return array
     */
    abstract public function getDefaultMetaData();
}
