<?php

/*
 * This file is part of the ChurchStreet LabCoat package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChurchStreet\LabCoat;

use GuzzleHttp\Client;

/**
 * ApiManager
 *
 * @author Tom Haskins-Vaughan <tom@tomhv.uk>
 * @since  0.1.0
 */
class ApiManager
{
    /**
     * apiToken
     *
     * @var string
     */
    private $apiToken;

    /**
     * baseUri
     *
     * @var string
     */
    private $baseUri;

    /**
     * client
     *
     * @var GuzzleHttp\Client
     */
    private $client;

    /**
     * __construct()
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @param string $apiToken
     * @param string $baseUri
     */
    public function __construct($apiToken, $baseUri)
    {
        $this->apiToken = $apiToken;
        $this->baseUri = $baseUri;
    }

    /**
     * Get client
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @return GuzzleHttp\Client
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->client = new Client([
                'base_uri' => $this->baseUri,
                'headers' => [
                    'PRIVATE-TOKEN' => $this->apiToken,
                ],
            ]);
        }

        return $this->client;
    }
}
