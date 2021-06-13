<?php
require_once(DOKU_INC . '/lib/plugins/issuetracker/lib/issueData.php');
if (!class_exists('IssueTrackerGitlabImplementation')) {
    class IssueTrackerGitlabImplementation
    {

        private $url;
        private $token;
        public function __construct($url, $token)
        {
            $this->url = $url;
            $this->token = $token;
        }
        public function loadData($query, $size, $page = 1)
        {
            $response = $this->doGet('/api/v4/'.$query);
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
            if(is_array($data)){
                foreach ($data as $item) {
                    $rdata->addIssue(
                        '#'.$item->iid,
                        $item->web_url,
                        $item->state,
                        $item->assignee->name,
                        $item->title
                    );
                }
                $rdata->setSize(count($data));
            }
            else{
                echo json_last_error();
                echo json_last_error_msg();
            }
            return $rdata;
        }
        private function doGET($uri)
        {
            $url = $this->url . $uri;
            $ch = curl_init();
            $options = [
                CURLOPT_URL => $url,
                CURLOPT_TIMEOUT => 5000,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_FOLLOWLOCATION => 1,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_0,
                CURLOPT_HEADER => 1,
                CURLOPT_HTTPHEADER => [
                    'accept: application/json;q=0.8,application/signed-exchange;v=b3;q=0.9',
                    'content-type: application/json',
                    'PRIVATE-TOKEN: '.$this->token
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
        private function getTest($fn){
            $res = new stdClass();
            $res->code = 200;
            $res->content = file_get_contents($fn);
            return $res;
        }       
    }
}
