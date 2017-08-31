<?php

return [

    /*
     * Use this secret to send request to this script e.g. www.domain.com/webhook.php?secret=qwerty123456qwerty123456qwerty123456
     */
    'auth' => [
        'secret' => 'qwerty123456qwerty123456qwerty123456',
    ],

    /*
     * All operations in jira will be done by this user
     */
    'jira' => [
        'url' => 'https://MY_JIRA_HOST.atlassian.net',
        'path' => '/rest/api/latest/issue/', //keep it like this
        'login' => 'andrey@gmail.zzz',
        'password' => 'qwerty123456',
        'transitions' => [
            /*
             * There will be different IDs in you workflow in your Jira. Find these IDs in your Jira.
             * This mapping here only because I want to use transitions names in ['workflow'] section of
             * this config to increase readability.
             *
             * Format:
             * 'Any name of transition' => Transition ID from your Jira
             */
            'Code Review done' => 4,
            'Changes requested' => 7,
        ],
    ],

    /*
     * Script will parse PR title to get Jira tasks by masks: [#KEY-2345] or [KEY-2345]
     */
    'projects' => ['KEY', 'B2B', 'CMS'], // [#KEY-2345],[KEY-2345],[#B2B-2345],[B2B-2345],[#CMS-2345],[CMS-2345]


    /*
     * Example of workflow. What script has to do if some labels added or removed from PR or Issue
     */
    'workflow' => [
        // Label name
        'code-reviewed' => [
            // If label added
            'labeled' => [
                'jira-transitions' => ['Code Review done'], // If label added we need to do "Code Review done" transition (see transitions mapping above)
                'blocked-by-labels' => ['comments'],        // But only if there is NO 'comments' label on this PR. So this label block this transition.
                'allowed-by-labels' => [],                  // These labels must be to be able to apply this transition
            ],
            // If label removed
            'unlabeled' => [
                'jira-transitions' => ['Changes requested'], // If label removed we need to do 'Changes requested' transition (see transitions mapping above)
                'blocked-by-labels' => [],
                'allowed-by-labels' => [],
            ],
        ],
        // if 'comments' label added/removed
        'comments' => [
            'labeled' => [
                'jira-transitions' => ['Changes requested'], // If label added we need to do 'Changes requested' transition (see transitions mapping above)
                'blocked-by-labels' => [],
                'allowed-by-labels' => [],
            ],
            'unlabeled' => [
                'jira-transitions' => ['Code Review done'], // If label removed we need to do 'Code Review done' transition (see transitions mapping above)
                'blocked-by-labels' => [],
                'allowed-by-labels' => ['code-reviewed'],   // But only if there IS 'code-reviewed' label on this PR
            ],
        ],
    ],

    'event-factory-mapping' => [
        'issues' => GithubToJira\GithubEvent\Issue::class,
        'pull_request' => GithubToJira\GithubEvent\PullRequest::class,
    ],
];