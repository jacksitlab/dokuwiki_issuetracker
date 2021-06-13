# Dokuwiki Issue Tracker Plugin

## Features
* Show issue table for specific filters.
* Supported servers:
  * JIRA
  * Gitlab
  * Github (TBD)

## Example - Jira

your config in local.php:
```
$conf['plugin']['issuetracker']['id'][0] = 'MyJira';
$conf['plugin']['issuetracker']['type'][0] = 'jira';
$conf['plugin']['issuetracker']['url'][0] = 'https://jira.my-domain.com';
$conf['plugin']['issuetracker']['apikey'][0] = '';
```

content:
```
{{issuetracker>MyJira|project = project1 AND labels = mylabel AND status != closed ORDER BY created DESC|15}}
```

which schows the issue table for the query ```project = project1 AND labels = mylabel AND status != closed ORDER BY created DESC``` with 15 rows.

## Example - Gitlab

your config in local.php:
```
$conf['plugin']['issuetracker']['id'][0] = 'MyGitlab';
$conf['plugin']['issuetracker']['type'][0] = 'gitlab';
$conf['plugin']['issuetracker']['url'][0] = 'https://git.my-domain.com';
$conf['plugin']['issuetracker']['apikey'][0] = 'your-private-access-token';
```

content:
```
{{issuetracker>MyGitlab|projects/37/issues?state=opened|20}}
```