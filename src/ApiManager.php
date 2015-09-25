<?php

/*
 * This file is part of the ChurchStreet LabCoat package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ChurchStreet\LabCoat;

use ChurchStreet\Model\Issue,
    ChurchStreet\Model\Resource;

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
     * metaData pattern
     *
     * @var array
     */
    private $pattern = '/<!--\DO NOT EDIT OR REMOVE THIS LINE({.*})-->/';

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

    /**
     * Get top-level Projects
     *
     * By default, these Projects have the string #TopLevelProject in the
     * description
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @return array|Project[]
     */
    public function getTopLevelProjects()
    {
        $response = $this->getClient()->get('projects', [
            'query' => [
                'search' => '#TopLevelProject',
            ],
        ]);

        $apiProjects = json_decode($response->getBody()->getContents(), true);

        $projects = [];

        foreach ($apiProjects as $apiProject) {
            $metaData = $this->getMetaData($apiProject);

            $projects[] = new Model\Project($apiProject, $metaData);
        }

        return $projects;
    }

    /**
     * Get Issues for a Project
     *
     * By default, we get 100 open issues
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @param Project $project
     *
     * @return array|Issue{}
     */
    public function getIssues(Model\Project $project, array $options = [])
    {
        $uri = sprintf('projects/%s/issues', $project->id);
        $defaultOptions = [
            'query' => [
                'state' => 'opened',
                'per_page' => 100,
            ],
        ];

        $options = array_merge_recursive($defaultOptions, $options);
        $response = $this->getClient()->get($uri, $options);
        $apiIssues = json_decode($response->getBody()->getContents(), true);

        $issues = [];

        foreach ($apiIssues as $apiIssue) {
            $issues[] = new Model\Issue($apiIssue, $this->getMetaData($apiIssue));
        }

        return $issues;
    }

    /**
     * Parse apiData for metaData
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @param array
     */
    public function getMetaData(array $apiData)
    {
        $metaData = [];

        $match = preg_match(
            $this->pattern,
            $apiData['description'],
            $matches
        );

        if ($match) {
            $metaData = json_decode($matches[1], true);
        }

        return $metaData;
    }
}
