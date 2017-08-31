<?php

namespace OldDeprecatedGitHubToJira;

class Jira
{
    private $jiraDomain = 'MY_JIRA_HOST.atlassian.net';
    private $jiraLogin = 'andrey@gmail.zzz';
    private $jiraPassword = 'qwerty123456';

    private $config = [
        'code-reviewed' => [
            'labeled' => [
                'jira-transitions' => [41, 51],
                'blocked-by-labels' => ['comments'],
                'allowed-by-labels' => [],
            ],
            'unlabeled' => [
                'jira-transitions' => [71],
                'blocked-by-labels' => [],
                'allowed-by-labels' => [],
            ],
        ],
        'comments' => [
            'labeled' => [
                'jira-transitions' => [61, 71],
                'blocked-by-labels' => [],
                'allowed-by-labels' => [],
            ],
            'unlabeled' => [
                'jira-transitions' => [41, 51],
                'blocked-by-labels' => ['question'],
                'allowed-by-labels' => ['code-reviewed'],
            ],
        ],
        'question' => [
            'labeled' => [
                'jira-transitions' => [61, 71],
                'blocked-by-labels' => [],
                'allowed-by-labels' => [],
            ],
            'unlabeled' => [
                'jira-transitions' => [41, 51],
                'blocked-by-labels' => ['comments'],
                'allowed-by-labels' => ['code-reviewed'],
            ],
        ],
    ];


    public function __construct(Request $gitHubRequest)
    {
        $actionConfig = $this->getConfig($gitHubRequest);
        if ($actionConfig) {
            if ($gitHubRequest->isLabelsNotExist($actionConfig['blocked-by-labels']) && $gitHubRequest->isLabelsExist($actionConfig['allowed-by-labels'])) {
                foreach ($gitHubRequest->getJiraIssuesIDs() as $jiraIssueID) {
                    foreach ($actionConfig['jira-transitions'] as $jiraTransitionID) {
                        var_dump($this->processTransition($jiraIssueID, $jiraTransitionID));
                    }
                }
            }
        }
    }

    private function getConfig(Request $gitHubRequest)
    {
        if (!empty($this->config[$gitHubRequest->getLabelName()]) && !empty($this->config[$gitHubRequest->getLabelName()][$gitHubRequest->getAction()])) {
            return $this->config[$gitHubRequest->getLabelName()][$gitHubRequest->getAction()];
        }

        return false;
    }

    private function processTransition($jiraIssueID, $transitionID)
    {
        $issueURL = $this->generateIssueURL($jiraIssueID) . '/transitions';
        $result = $this->callJiraURL(
            $issueURL,
            json_encode(["transition" => ['id' => $transitionID]])
        );
        return $result;
    }

    private function generateIssueURL($jiraIssueID)
    {
        return 'https://' . $this->jiraDomain . '/rest/api/latest/issue/' . $jiraIssueID;
    }

    private function callJiraURL($url, $data = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->jiraLogin . ':' . $this->jiraPassword);

        if (!empty($data)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }
}


class Request
{
    private $secret = 'qwerty123456qwerty123456qwerty123456';
    private $jiraProjectsKeyRegexp = '/\[#?((KEY)\-\d+)\]/im';

    private $requestJson = [];

    public function __construct()
    {
        $this->requestJson = json_decode(file_get_contents('php://input'), true);
        if (empty($this->requestJson)) {
            throw new \Exception('Request', 1);
        }

        if (empty($_GET['secret']) || $_GET['secret'] !== $this->secret) {
            throw new \Exception('Request', 2);
        }
    }

    public function getJiraIssuesIDs()
    {
        if (empty($this->getIssueData()['title'])) {
            throw new \Exception('Request', 3);
        }

        preg_match_all($this->jiraProjectsKeyRegexp, $this->getIssueData()['title'], $matches);
        $jiraIssuesIDs = array_unique($matches[1]);

        if (empty($jiraIssuesIDs)) {
            throw new \Exception('Request', 4);
        }

        return $jiraIssuesIDs;
    }

    public function getLabelName()
    {
        if (empty($this->requestJson['label']['name'])) {
            throw new \Exception('Request', 5);
        }

        return $this->requestJson['label']['name'];
    }

    public function getAction()
    {
        if (empty($this->requestJson['action'])) {
            throw new \Exception('Request', 6);
        }

        return $this->requestJson['action'];
    }

    private function getExistedLabels()
    {
        if (empty($this->getIssueData()['labels'])) {
            return [];
        }

        return $this->getIssueData()['labels'];
    }

    public function isLabelsExist(array $labels)
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

    public function isLabelsNotExist(array $labels)
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

    private function getIssueData()
    {
        if (!empty($this->requestJson['pull_request'])) {
            return $this->requestJson['pull_request'];
        } elseif (!empty($this->requestJson['issue'])) {
            return $this->requestJson['issue'];
        } else {
            throw new \Exception('Request', 7);
        }
    }
}

try {
    new Jira(new Request());
} catch (\Exception $e) {
    echo $e->getMessage() . ' ' . $e->getCode();
}
