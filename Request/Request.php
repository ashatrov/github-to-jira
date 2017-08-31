<?php
namespace GithubToJira\Request;

class Request
{
    /**
     * @var array
     */
    private $requestJson = [];

    /**
     * @param string $secret
     * @throws RequestException
     */
    public function __construct($secret)
    {
        if (empty($_GET['secret']) || $_GET['secret'] !== $secret) {
            throw new RequestException('Wrong Secret', RequestException::BAD_REQUEST);
        }

        $input = file_get_contents('php://input');
        $this->requestJson = json_decode($input, true);

        if (empty($this->requestJson)) {
            throw new RequestException('Not JSON', RequestException::BAD_REQUEST);
        }
    }

    /**
     * @return string
     * @throws RequestException
     */
    public function getEventType() {
        if (empty($_SERVER['HTTP_X_GITHUB_EVENT']) || $this->requestJson[$_SERVER['HTTP_X_GITHUB_EVENT']]) {
            throw new RequestException('Wrong Event', RequestException::BAD_REQUEST);
        }

        return $_SERVER['HTTP_X_GITHUB_EVENT'];
    }

    /**
     * @return array
     */
    public function getData() {
        return $this->requestJson;
    }
}