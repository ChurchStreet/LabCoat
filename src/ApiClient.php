<?php

/*
 * This file is part of the ChurchStreet LabCoat package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChurchStreet\LabCoat;

/**
 * ApiClient
 *
 * @author Tom Haskins-Vaughan <tom@tomhv.uk>
 * @since  0.1.0
 */
class ApiClient
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
     * httpClient
     *
     * @var GuzzleHttp\Client
     */
    private $httpClient;

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
     * Get httpClient
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @return GuzzleHttp\Client
     */
    private function getHttpClient()
    {
        if (!$this->httpClient) {
            $this->httpClient = new \GuzzleHttp\Client([
                'base_uri' => $this->baseUri,
                'headers' => [
                    'PRIVATE-TOKEN' => $this->apiToken,
                ],
            ]);
        }

        return $this->httpClient;
    }

    /**
     * Get single issue by iid
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @param int $project_id
     * @param int $issue_iid
     *
     * @return array
     */
    public function getProjectIssueByIid($project_id, $issue_iid)
    {
        $uri = sprintf('projects/%s/issues', $project_id);

        $response = $this->getHttpClient()->get($uri, [
            'query' => ['iid' => $issue_iid],
        ]);

        $issues = json_decode($response->getBody()->getContents(), true);

        if (!count($issues)) {
            throw new ResourceNotFoundException(sprintf(
                'No Issue found for iid "%s"',
                $issue_iid
            ));
        }

        return $issues[0];
    }
}
