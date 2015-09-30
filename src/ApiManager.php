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
     * metaData pattern
     *
     * @var array
     */
    private $pattern = '/<!--DO NOT EDIT OR REMOVE THIS LINE\(({.*})\)-->/';

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
     * Get Project
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @param string $namespace
     * @param string $name
     *
     * @return Project
     */
    public function getProject($namespace, $name)
    {
        $uri = sprintf('projects/%s%s%s',
            $namespace,
            '%2F',
            $name
        );
        $response = $this->getClient()->get($uri);
        $apiProject = json_decode($response->getBody()->getContents(), true);
        $metaData = $this->getMetaData($apiProject);

        return new Model\Project($apiProject, $metaData);
    }

    /**
     * Get single Issue for a Project
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @param Project $project
     * @param int     $id
     *
     * @return array|Issue
     */
    public function getIssue(Model\Project $project, $id)
    {
        $uri = sprintf('projects/%s/issues/%s', $project->id, $id);

        $response = $this->getClient()->get($uri);
        $apiIssue = json_decode($response->getBody()->getContents(), true);

        return new Model\Issue($apiIssue, $this->getMetaData($apiIssue));
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

    /**
     * Put metaData for Resource
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @param Resource $resource
     * @param array    $metaData
     */
    public function putMetaData(Model\Resource $resource, array $metaData)
    {
        $metaData = array_merge($resource->getMetaData(), $metaData);
        $metaJson = sprintf('<!--DO NOT EDIT OR REMOVE THIS LINE(%s)-->',
            json_encode($metaData)
        );

        $match = preg_match(
            $this->pattern,
            $resource->description,
            $matches
        );

        if ($match) {
            // remove existing metadata
            $resource->description = trim(str_replace($matches[0], '', $resource->description));
        }

        // compare existing to new metaData
        $changes = $this->compareMetaData($resource->getMetaData(), $metaData);

        // prepend new metaData
        $description = sprintf("%s\n%s",
            $metaJson,
            $resource->description
        );

        $uri = sprintf('projects/%s/issues/%s', $resource->project_id, $resource->id);
        $response = $this->getClient()->put($uri, [
            'json' => [
                'description' => $description,
            ]
        ]);

        // log changes
        $uri = sprintf('projects/%s/issues/%s/notes', $resource->project_id, $resource->id);
        foreach ($changes as $property => $change) {
            $response = $this->getClient()->post($uri, [
                'json' => [
                    'body' => sprintf(
                        'Changed **%s** from **%s** to **%s** in LabCoat',
                        ucfirst($property),
                        $change['existing'],
                        $change['new']
                    ),
                ]
            ]);
        }

    }

    /**
     * Compare metaData
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @param array $existing
     * @param array $new
     *
     * @return array changes
     */
    public function compareMetaData(array $existing, array $new)
    {
        $changes = [];

        foreach ($new as $key => $value) {
            if (array_key_exists($key, $existing)) {
                if ($new[$key] != $existing[$key]) {
                    $changes[$key] = [
                        'existing' => $existing[$key],
                        'new' => $new[$key],
                    ];
                }
            }
        }

        return $changes;
    }
}
