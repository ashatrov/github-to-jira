<?php
namespace GithubToJira;

class Jira
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param array $tasksIDs
     * @param array $transitions
     * @return array
     */
    public function transitTasks(array $tasksIDs, array $transitions) {
        $results = [];
        foreach ($tasksIDs as $tasksID) {
            foreach ($transitions as $transitionName) {
                $results[] = $this->transitTask($tasksID, $transitionName);
            }
        }

        return $results;
    }

    /**
     * @param $tasksID
     * @param $transitionName
     * @return mixed
     */
    private function transitTask($tasksID, $transitionName)
    {
        $issueURL = $this->generateIssueURL($tasksID) . '/transitions';
        $result = $this->callJiraURL(
            $issueURL,
            json_encode(["transition" => ['id' => $this->resolveTransitionId($transitionName)]])
        );
        return $result;
    }

    private function generateIssueURL($jiraIssueID)
    {
        return $this->config['url'] . $this->config['path'] . $jiraIssueID;
    }

    /**
     * @param string $transitionName
     * @return int
     */
    private function resolveTransitionId($transitionName)
    {
        return $this->config['transitions'][$transitionName];
    }

    /**
     * @param $url
     * @param null $data
     * @return mixed
     */
    private function callJiraURL($url, $data = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->config['login'] . ':' . $this->config['password']);

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