<?php
/**
 * Copyright: Deux Huit Huit 2017
 * License: MIT, see the LICENSE file
 */

if (!defined("__IN_SYMPHONY__")) die("<h2>Error</h2><p>You cannot directly access this file</p>");

require_once(EXTENSIONS . '/amp/vendor/autoload.php');

use Lullabot\AMP\AMP;
use Lullabot\AMP\Validate\Scope;

class extension_amp extends Extension
{
    /**
     * Name of the extension
     * @var string
     */
    const EXT_NAME = 'AMP';

    const AMP_REGEX = '/\/amp\/?$/';

    private $devKitActive = false;

    /* ********* INSTALL/UPDATE/UNISTALL ******* */

    /**
     * Creates the table needed for the settings of the field
     */
    public function install()
    {
        return true;
    }
    
    /**
     * Creates the table needed for the settings of the field
     */
    public function update($previousVersion = false)
    {
        $ret = true;
        return $ret;
    }

    /**
     *
     * Drops the table needed for the settings of the field
     */
    public function uninstall()
    {
        return true;
    }

    /*------------------------------------------------------------------------------------------------*/
    /*  Delegates  */
    /*------------------------------------------------------------------------------------------------*/

    public function getSubscribedDelegates()
    {
        return array(
            array(
                'page'     => '/frontend/',
                'delegate' => 'FrontendPrePageResolve',
                'callback' => 'frontendPrePageResolve'
            ),
            array(
                'page'     => '/frontend/',
                'delegate' => 'FrontendPageResolved',
                'callback' => 'frontendPageResolved'
            ),
            array(
                'page'     => '/frontend/',
                'delegate' => 'FrontendParamsPostResolve',
                'callback' => 'frontendParamsPostResolve'
            ),
            array(
                'page'     => '/frontend/',
                'delegate' => 'FrontendOutputPostGenerate',
                'callback' => 'frontendOutputPostGenerate'
            ),
            array(
                'page'      => '/frontend/',
                'delegate'  => 'FrontendDevKitResolve',
                'callback'  => 'frontendDevKitResolve'
            ),
            array(
                'page'      => '/frontend/',
                'delegate'  => 'ManipulateDevKitNavigation',
                'callback'  => 'manipulateDevKitNavigation'
            ),
        );
    }

    public function frontendPrePageResolve(array $context)
    {
        $page = $context['page'];
        if (preg_match(self::AMP_REGEX, $page)) {
            $context['page'] = preg_replace(self::AMP_REGEX, '/', $page, 1);
            $ru = explode('?', server_safe('REQUEST_URI'));
            $ru[0] = preg_replace(self::AMP_REGEX, '/', $ru[0], 1);
            $_SERVER['REQUEST_URI'] = implode('?', $ru);
            $this->setEnv('amp', true);
        }
    }

    public function frontendPageResolved(array $context)
    {
        if ($this->isAMP()) {
            $data = &$context['page_data'];
            if ($this->isNoAMP($data)) {
                throw new FrontendPageNotFoundException;
            }
            $fl = $data['filelocation'];
            if ($fl) {
                $fl = preg_replace('/\.xsl$/', '_amp.xsl', $fl);
                if (@file_exists($fl)) {
                    $data['filelocation'] = $fl;
                }
            }
        }
    }

    public function frontendParamsPostResolve(array $context)
    {
        Frontend::Page()->_param['amp'] = $this->isAMP() ? 'Yes' : 'No';
    }

    public function frontendOutputPostGenerate(array $context)
    {
        if ($this->isAMP() && !Frontend::Page()->Proc->isErrors()) {
            $preRegExp = Symphony::Configuration()->get('pre-regexp', 'amp');
            $postRegExp = Symphony::Configuration()->get('post-regexp', 'amp');
            $output = &$context['output'];
            if (!empty($preRegExp)) {
                $startTime = precision_timer();
                $this->executeRegexp($preRegExp, $output);
                $this->lap($startTime, 'AMP Pre RegExp execution');
            }
            $startTime = precision_timer();
            $amp = new AMP();
            $amp->loadHtml($output, array('scope' => Scope::HTML_SCOPE));
            $context['output'] = $amp->convertToAmpHtml();
            $errors = $amp->warningsHumanHtml();
            $this->setEnv('amp-errors', $errors);
            $this->lap($startTime, 'AMP Generation');
            if (!empty($postRegExp)) {
                $startTime = precision_timer();
                $this->executeRegexp($postRegExp, $output);
                $this->lap($startTime, 'AMP Post RegExp execution');
            }
        }
    }

    private function executeRegexp($regexps, &$output)
    {
        if (!is_array($regexps)) {
            return;
        }
        foreach ($regexps as $regexp => $replacement) {
            $output = preg_replace($regexp, $replacement, $output);
        }
    }

    private function lap($startTime, $msg)
    {
        Symphony::Profiler()->seed($startTime);
        Symphony::Profiler()->sample($msg, PROFILE_LAP, self::EXT_NAME);
    }

    private function setEnv($key, $value)
    {
        $env = Frontend::Page()->Env();
        $env[$key] = $value;
        Frontend::Page()->setEnv($env);
    }

    private function isAMP()
    {
        $env = Frontend::Page()->Env();
        return isset($env['amp']) && $env['amp'];
    }

    private function isNoAMP(array $data)
    {
        return General::in_iarray('no-amp', $data['type']);
    }

    public function frontendDevKitResolve(array $context)
    {
        if (isset($_GET['debug-amp'])) {
            require_once(EXTENSIONS . '/amp/content/content.ampdevkit.php');

            $context['devkit'] = new Content_AmpDevKit();
            $this->devKitActive = true;
        }
    }

    public function manipulateDevKitNavigation(array $context)
    {
        $xml = $context['xml'];
        $item = $xml->createElement('item');
        $item->setAttribute('name', __(self::EXT_NAME));
        $item->setAttribute('handle', 'debug-amp');
        $item->setAttribute('active', ($this->devKitActive ? 'yes' : 'no'));

        $xml->documentElement->appendChild($item);
    }
}
