<?php
namespace GithubToJira\GithubEvent;

class PullRequest extends Issue
{
    const EVENT_TYPE = 'pull_request';
}