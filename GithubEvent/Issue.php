<?php
namespace GithubToJira\GithubEvent;

use GithubToJira\Request\Request;

class Issue
{
    const EVENT_TYPE = 'issue';

    /**
     * @var array
     */
    private $projectsList;

    /**
     * @var array
     */
    private $workflowConfig;

    /**
     * @var
     */
    private $data;

    /**
     * @var string
     */
    protected $jiraProjectsKeyRegexp = '/\[#?((%s)\-\d+)\]/im';

    /**
     * @param array $config
     * @param Request $request
     */
    public function __construct(array $config, Request $request)
    {
        $this->projectsList = $config['projects'];
        $this->workflowConfig = $config['workflow'];
        $this->setEventData($request->getData());
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getJiraTaskIDs()
    {
        $jiraIssuesIDs = [];
        foreach ($this->projectsList as $projectName) {
            preg_match_all(
                sprintf($this->jiraProjectsKeyRegexp, $projectName),
                $this->getTile(),
                $matches
            );
            $jiraIssuesIDs = array_merge($jiraIssuesIDs, $matches[1]);
        }
        $jiraIssuesIDs = array_unique($jiraIssuesIDs);

        if (empty($jiraIssuesIDs)) {
            throw new \Exception('No Jira Issue ID');
        }

        return $jiraIssuesIDs;
    }

    /**
     * @return array
     */
    private function getActionConfig() {
        if (!empty($this->workflowConfig[$this->getLabelName()]) && !empty($this->workflowConfig[$this->getLabelName()][$this->getLabelAction()])) {
            return $this->workflowConfig[$this->getLabelName()][$this->getLabelAction()];
        }

        return [];
    }

    /**
     * @return array
     */
    public function getAllowedTransitions() {
        $actionConfig = $this->getActionConfig();

        if ($actionConfig) {
            if ($this->isLabelsNotExist($actionConfig['blocked-by-labels']) && $this->isLabelsExist($actionConfig['allowed-by-labels'])) {
                return $actionConfig['jira-transitions'];
            }
        }

        return [];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getLabelName()
    {
        if (empty($this->data['label']['name'])) {
            throw new \Exception('No label');
        }

        return $this->data['label']['name'];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getLabelAction()
    {
        if (empty($this->data['action'])) {
            throw new \Exception('No Label Action');
        }

        return $this->data['action'];
    }

    /**
     * @return array
     */
    private function getExistedLabels()
    {
        if (empty($this->data['labels'])) {
            return [];
        }

        return $this->data['labels'];
    }

    /**
     * @param array $labels
     * @return bool
     */
    private function isLabelsExist(array $labels)
    {
        if (empty($labels)) {
            return true;
        }

        foreach ($this->getExistedLabels() as $existedLabel) {
            if (array_search($existedLabel['name'], $labels) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $labels
     * @return bool
     */
    private function isLabelsNotExist(array $labels)
    {
        if (empty($labels)) {
            return true;
        }

        foreach ($this->getExistedLabels() as $existedLabel) {
            if (array_search($existedLabel['name'], $labels) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $data
     * @throws \Exception
     */
    private function setEventData(array $data)
    {
        if (!empty($data[self::EVENT_TYPE])) {
            $this->data = $data[self::EVENT_TYPE];
        } else {
            throw new \Exception('Wrong data');
        }
    }

    /**
     * @return string
     * @throws \Exception
     */
    private function getTile() {
        if (empty($this->data['title'])) {
            throw new \Exception('No title');
        }

        return $this->data['title'];
    }
}