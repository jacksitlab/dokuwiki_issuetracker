<?php
require_once(DOKU_INC . '/lib/plugins/issuetracker/lib/issueData.php');
if (!class_exists('IssueTrackerJiraImplementation')) {
    class IssueTrackerJiraImplementation
    {

        private $url;
        public function __construct($url)
        {
            $this->url = $url;
        }
        public function loadData($query, $size, $page = 1)
        {
            $payload = new stdClass();
            $payload->jql=$query;
            $payload->startAt = 0;
            $payload->maxResults = $size;
            $payload->fields=[ "summary","status","assignee","status"];

            $response = $this->doPost('/rest/api/2/search',$payload);
            if($response == false || $response->code != 200){
                return "bad response code ".$response->code;
            }
            $data = $this->mapData(json_decode($response->content));
            $data->setPage($page);
            return $data;
        }
        private function mapData($data)
        {
            $rdata = new IssueTrackerIssueData();
            foreach ($data->issues as $item) {
                $rdata->addIssue(
                    $item->key,
                    $this->url . '/browse/' . $item->key,
                    $item->fields->status->name,
                    $item->fields->assignee->displayName,
                    $item->fields->summary
                );
            }
            $rdata->setSize($data->total);
            return $rdata;
        }
        private function doPost($uri, $data)
        {
            $url = $this->url . $uri;
            $ch = curl_init();
            $options = [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_POSTFIELDS => json_encode($data),
                CURLOPT_AUTOREFERER => 1,
                CURLOPT_TIMEOUT => 5000,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0,
                CURLOPT_HEADER => 1,
                CURLOPT_HTTPHEADER => [
                    'accept: application/json;q=0.8,application/signed-exchange;v=b3;q=0.9',
                    'content-type: application/json'
                ]
            ];
            curl_setopt_array($ch, $options);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            curl_close($ch);
            if (!$response) {
                return false;
            }
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);
            $res = new stdClass();
            $res->code = $httpCode;
            $res->content = $body;
            return $res;
        }
    }
}
