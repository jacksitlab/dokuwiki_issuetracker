<?php
if (!class_exists('IssueTrackerIssueData')) {
    class IssueTrackerIssueData
    {

        public $issues;
        public $size;
        public $page;
        
        public function __construct()
        {
            $this->issues = [];
            $this->size = 0;
            $this->page = 0;
        }
        public function setSize($size)
        {
            $this->size = $size;
            return $this;
        }
        public function setPage($page)
        {
            $this->page = $page;
            return $this;
        }
        public function addIssue($key, $url, $status, $assignee, $summary, $icon = '')
        {
            $this->issues[] = new IssueTrackerIssue($key, $url, $status, $assignee, $summary, $icon);
        }
    }

    class IssueTrackerIssue
    {

        public $key;
        public $url;
        public $status;
        public $summary;
        public $icon;
        public $assignee;

        public function __construct($key, $url, $status, $assignee, $summary, $icon)
        {
            $this->key = $key;
            $this->url = $url;
            $this->status = $status;
            $this->assignee = $assignee;
            $this->summary = $summary;
            $this->icon = $icon;
        }
    }
}
