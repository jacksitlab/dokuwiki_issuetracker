<?php

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_issuetracker_toolbar extends DokuWiki_Action_Plugin {

    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     * @return void
     */
    public function register(Doku_Event_Handler $controller) {

       $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'insert_button', array ());
   
    }

    /**
     * Handles the "New IssueTracker table" button.
     *
     * @param Doku_Event $event  event object by reference
     * @param mixed      $param  ignored
     * @return void
     */

    public function insert_button(Doku_Event &$event, $param) {
        $event->data[] = array (
            'type' => 'format',
            'icon' => '../../plugins/issuetracker/res/toolbar_icon.png',
            'title' => htmlspecialchars('Issue Tracker Entry'),
            'open' => '{{issuetracker>',
            'close' => '}}',
            'sample' => 'id|myquery|mysize',
        );
    }

}

// vim:ts=4:sw=4:et:
