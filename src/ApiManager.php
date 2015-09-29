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
     * @var ChurchStreet\LabCoat\ApiClient
     */
    private $client;

    /**
     * _client
     *
     * @var ChurchStreet\LabCoat\ApiClient
     */
    private $_client;

    /**
     * __construct()
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @param string    $apiToken
     * @param string    $baseUri
     * @param ApiClient $client
     */
    public function __construct($apiToken, $baseUri, $client)
    {
        $this->apiToken = $apiToken;
        $this->baseUri = $baseUri;
        $this->client = $client;
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
        if (!$this->_client) {
            $this->_client = new Client([
                'base_uri' => $this->baseUri,
                'headers' => [
                    'PRIVATE-TOKEN' => $this->apiToken,
                ],
            ]);
        }

        return $this->_client;
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
     * Get Milestone
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @param Project $project
     * @param int     $milestone_iid
     *
     * @return Milestone
     */
    public function getMilestone(Model\Project $project, $milestone_iid)
    {
        $milestone = $this->client->getProjectMilestoneById($project->id, $milestone_iid);

        $metaData = $this->getMetaData($milestone);

        return new Model\Milestone($milestone, $metaData);
    }

    /**
     * Get single Issue for a Project
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @param Project $project
     * @param int     $issue_iid
     *
     * @return Issue
     */
    public function getIssue(Model\Project $project, $issue_iid)
    {
        $issue = $this->client->getProjectIssueByIid($project->id, $issue_iid);

        return new Model\Issue($issue, $this->getMetaData($issue));
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
     * Get Issues that have no Milestone for a Project
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @param Project $project
     *
     * @return array
     */
    public function getIssuesWithNoMilestone(Model\Project $project, array $options = [])
    {
        $uri = sprintf('projects/%s/issues', $project->id);
        $defaultOptions = [
            'query' => [
                'state' => 'opened',
                'per_page' => 100,
            ],
        ];

        $issues = [
            Model\Issue::PRIORITY_HIGH => [
                'estimatedHours' => 0,
                'count' => 0,
                'issues' => [],
            ],
            Model\Issue::PRIORITY_MEDIUM => [
                'estimatedHours' => 0,
                'count' => 0,
                'issues' => [],
            ],
            Model\Issue::PRIORITY_LOW => [
                'estimatedHours' => 0,
                'count' => 0,
                'issues' => [],
            ],
        ];

        $options = array_merge_recursive($defaultOptions, $options);
        $response = $this->getClient()->get($uri, $options);
        $apiIssues = json_decode($response->getBody()->getContents(), true);

        foreach ($apiIssues as $apiIssue) {
            if (!$apiIssue['milestone']) {
                $issue = new Model\Issue($apiIssue, $this->getMetaData($apiIssue));

                $issues[$issue->priority]['estimatedHours'] += $issue->estimated;
                $issues[$issue->priority]['count']++;
                $issues[$issue->priority]['issues'][] = $issue;
            }
        }

        return $issues;
    }

    /**
     * Get Milestones for a Project
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @param Project $project
     *
     * @return array|Milestone[]
     */
    public function getMilestonesForProject(Model\Project $project, array $options = [])
    {
        $uri = sprintf('projects/%s/milestones', $project->id);
        $defaultOptions = [
            'query' => [
                'per_page' => 100,
            ],
        ];

        $options = array_merge_recursive($defaultOptions, $options);
        $response = $this->getClient()->get($uri, $options);
        $apiMilestones = json_decode($response->getBody()->getContents(), true);

        $milestones = [];

        foreach ($apiMilestones as $apiMilestone) {
            if ('active' == $apiMilestone['state']) {
                $milestones[] = new Model\Milestone($apiMilestone, $this->getMetaData($apiMilestone));
            }
        }

        return $milestones;
    }

    /**
     * Get Issues for a Milestone
     *
     * @author Tom Haskins-Vaughan <tom@tomhv.uk>
     * @since  0.1.0
     *
     * @param Project $project
     *
     * @return array|Issue[]
     */
    public function getIssuesForMileStone(Model\Milestone $milestone, array $options = [])
    {
        $uri = sprintf('projects/%s/issues', $milestone->project_id);
        $defaultOptions = [
            'query' => [
                'milestone' => $milestone->title,
                'per_page' => 100,
            ],
        ];

        $options = array_merge($defaultOptions, $options);
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
    }
}
