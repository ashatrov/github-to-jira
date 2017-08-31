<?php
namespace GithubToJira\GithubEvent;

use GithubToJira\Request\Request;

class EventFactory
{
    /**
     * @var Issue|PullRequest
     */
    private $event;

    /**
     * @param array $config
     * @param Request $request
     * @throws \Exception
     */
    public function __construct(array $config, Request $request)
    {
        $eventType = $request->getEventType();

        if ($config['event-factory-mapping'][$eventType]) {
            $this->event = new $config['event-factory-mapping'][$eventType]($config, $request);
        } else {
            throw new \Exception('Unsupported Event');
        }
    }

    /**
     * @return Issue|PullRequest
     */
    public function getEvent()
    {
        return $this->event;
    }
}