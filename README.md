# github-to-jira

*Not flexible script for personal usage only*

It will trigger transitions in Jira to move issue from one status to second if specific label added or removed from pull request or issue on GitHub.

1. Add webhook in GitHub to http://host.zz/github-to-jira.php?secret=qwerty123456qwerty123456qwerty123456
2. Trigger this webhook only if Pull Request or Issue changed (label added, label removed)
3. Create Pull Request with title by mask "[#KEY-345] Integrate GitHub and Jira"
4. Add label 'code-reviewed' to this Pull Request
5. Transitions with ID 41 and 51 will be triggered in Jira
6. Add 'comments' label or remove 'code-reviewed' and transitions 61 and 71 will we triggered
7. If you remove label 'comments', transitions 41 and 51 will be triggered only if there is 'code-reviewed' already present and no 'question' label.

There are 2 classes in one file and both of them have option to configure.

*Use you own*  
jiraDomain  
jiraLogin  
jiraPassword  
config  
secret  
jiraProjectsKeyRegexp  

