# github-to-jira

**It will trigger transitions in Jira to move Tasks from one status to other if specific label added or removed from Pull Request or Issue on GitHub.**

## Setup webhook on GitHub
1. Add the webhook in GitHub to `http://your-host.zz/webhook.php?secret=qwerty123456qwerty123456qwerty123456` (change Secret in `config.php`). Don't use Secret field on this page. Keep secret in webhook url!  
   Content type `application/json`  

   On the page `Settings/Webhooks/Add webhook` choose `Let me select individual events.` and add checkboxes to `Issue` and `Pull Request` (webhook will be triggered on label added/label removed)

## config.php
1. **Copy `config.dist.php` to `config.php`**
1. Change `['auth']['secret']` to your long random private string
1. Setup your Jira url, user, password in `['jira']`
1. Setup jira transitions ID to transition names mapping `['jira']['transitions']`  
   There will be different IDs in you workflow in your Jira. Find these IDs in your Jira.
   This mapping here only because I want to use transitions names in ['workflow'] section in config.php to increase readability.
1. Change list of project keys/IDs from your Jira `['projects']`
1. Describe your workflow in `['workflow']`. Read comments in config.php!

## How to use webhook
1. Change `config.php`
1. Create new Task in Jira (e.g. tsks ID will be KEY-345)
1. Create Pull Request or Issue on GitHub with title "[#KEY-345] Integrate GitHub and Jira"
1. Add label 'code-reviewed' to this Pull Request
1. The transition with ID#4 will be triggered in Jira for the tasks KEY-345. (Your Jira has different transitions with different IDs! You can change mapping in `config.php` in ['jira']['transitions'])
1. Add 'comments' label or remove 'code-reviewed' label and transition ID#7 will we triggered in Jira for the Tasks KEY-345
1. If you remove label 'comments', transition ID#4 will be triggered only if 'code-reviewed' already present for this Pull Request or Issue.

