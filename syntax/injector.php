<?php

if (!defined('DOKU_INC')) die();

require_once(DOKU_INC . '/lib/plugins/issuetracker/lib/jira.php');
require_once(DOKU_INC . '/lib/plugins/issuetracker/lib/gitlab.php');

class syntax_plugin_issuetracker_injector extends DokuWiki_Syntax_Plugin
{
    private static $OPENING_TAG = '{{issuetracker>';
    private static $CLOSING_TAG = '}}';
    private static $DEFAULT_SIZE = 20;
    /**
     * @return string Syntax mode type
     */
    public function getType()
    {
        return 'substition';
    }
    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort()
    {
        return 200;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode)
    {
        $this->Lexer->addSpecialPattern(self::$OPENING_TAG . '\n*.*?\n*' . self::$CLOSING_TAG, $mode, 'plugin_issuetracker_injector');
    }

    /**
     * Handle matches of the issuetracker syntax
     *
     * @param string          $match   The match of the syntax
     * @param int             $state   The state of the handler
     * @param int             $pos     The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
    {
        $config = str_replace(self::$CLOSING_TAG, '', str_replace(self::$OPENING_TAG, '', $match));
        $ids   = $this->getConf('id');
        $types   = $this->getConf('type');
        $urls   = $this->getConf('url');
        $apikeys   = $this->getConf('apikey');
        $query = '';
        $data = [];
        $key = false;
        $errormessage = "";
        $conf = [];
        $matches=explode('|',$config);
        if (count($matches) > 1) {
            $id = $matches[0];
            $query = $matches[1];
            
            $size =count($matches) > 1?$matches[2]:self::$DEFAULT_SIZE;
        } else {
            $errormessage .= "no id detected in " . $config . "\n";
        }
        $key = array_search($id, $ids);
        $provider = null;
        if ($key !== false) {
            $conf['id'] = $id;
            $conf['url'] = $urls[$key];
            $conf['type'] = $types[$key];
            $conf['apikey'] = $apikeys[$key];
            if($conf['type'] =='jira'){
                $provider = new IssueTrackerJiraImplementation($conf['url']);
            }
            elseif($conf['type'] =='gitlab'){
                $provider = new IssueTrackerGitlabImplementation($conf['url'], $conf['apikey']);
            }
        } else {
            $errormessage .= "id '" . $id . "'not configured\n";
        }
        if ($provider != null) {

            try {
                $data = $provider->loadData($query, $size);
                if(is_string($data)){
                    $errormessage.=$data."\n";
                    $data=null;
                }
            } catch (Exception $e) {
                $errormessage .= $e;
            }
        }
        else{
            $errormessage.="type '".$conf['type'].' is not implemented\n';
        }
        return [
            'error' => $errormessage,
            'conf' => $conf,
            'query' => $query,
            'data' => $data
        ];
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string         $mode      Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer  $renderer  The renderer
     * @param array          $data      The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer $renderer, $data)
    {

        if ($mode != 'xhtml') return false;
        //$renderer->nocache();

       if (strlen($data['error'])) {
            $renderer->doc .= "<span class='warn'>ERROR: " . $data['error'] . "</span>";
            return true;
        }
        if($data['data']!=null && !is_array($data['data']->issues)){
            $renderer->doc .= "<span class='warn'>ERROR: bad response from remote host</span>";
            return true;
        }
        $renderer->doc .= "<div><table class='issuetracker'><thead><tr><td>Issue</td><td>Summary</td><td>Status</td><td>Assignee</td></tr></thead><tbody>";
        foreach($data['data']->issues as $issue) {
            $renderer->doc .= '<tr><td><a href="'.$issue->url.'" target="_blank">'.$issue->key.'</a></td>'.
            '<td>'.$issue->summary.'</td>'.
            '<td>'.$issue->status.'</td>'.
            '<td>'.$issue->assignee.'</td></tr>'; 
        }
        $renderer->doc .= "</tbody></table></div>";


        return true;
    }
}
