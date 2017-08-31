<?php
namespace GithubToJira;

include_once 'bootstrap.php';

use GithubToJira\GithubEvent\EventFactory;
use GithubToJira\Request\Request;

$config = include 'config.php';

$request = new Request($config['auth']['secret']);

$event = (new EventFactory($config, $request))->getEvent();

$jiraTaskIDs = $event->getJiraTaskIDs();
$transitions = $event->getAllowedTransitions();

if (!empty($jiraTaskIDs) && !empty($transitions)) {
    $jira = new Jira($config['jira']);
    var_dump($jiraTaskIDs, $transitions);
    var_dump($jira->transitTasks($event->getJiraTaskIDs(), $event->getAllowedTransitions()));
}

