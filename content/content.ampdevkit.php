<?php
/**
 * Copyright: Deux Huit Huit 2017
 * License: MIT, see the LICENSE file
 */

if (!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

class Content_AmpDevKit extends DevKit
{
    public function __construct()
    {
        parent::__construct();
        $this->_title = __('AMP Debug');
    }

    public function build()
    {
        $this->addStylesheetToHead(URL . '/extensions/amp/assets/ampdevkit.css', 'screen', 10);
        return parent::build();
    }

    protected function buildContent($wrapper)
    {
        $env = Frontend::Page()->Env();
        if (empty($env['amp']) || !$env['amp']) {
            $uri = explode('?', server_safe('REQUEST_URI'));
            $uri[0] .= 'amp/';
            $uri = implode('?', $uri);
            $h1 = new XMLElement('h1', "This page is not amp powered. <a href=\"{$uri}\">Try this one</a>.");
            $wrapper->appendChild($h1);
            return;
        }
        $h1 = new XMLElement('h1', 'Profile');
        $wrapper->appendChild($h1);
        $laps = Symphony::Profiler()->retrieveGroup('AMP');
        foreach ($laps as $lap) {
            $p = new XMLElement('p', $lap[0] . ': ' . $lap[1] . ' s');
            $wrapper->appendChild($p);
        }
        $h1 = new XMLElement('h1', 'Errors');
        $wrapper->appendChild($h1);
        $pre = new XMLElement('pre');
        $code = new XMLElement('code', $env['amp-errors']);
        $pre->appendChild($code);
        $wrapper->appendChild($pre);
    }
}
