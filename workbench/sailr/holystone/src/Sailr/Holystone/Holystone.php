<?php namespace Sailr\Holystone;

use Sailr\Support;
use Illuminate\Support\Facades\Config;

class Holystone implements HolystoneInterface {

    protected $htmlPurifier;
    protected $holystoneConfig;
    protected $markdownParser;
    public $configuration;

    public function __construct() {
        //$this->markdownParser = new \Parsedown();
        $this->holystoneConfig = \HTMLPurifier_Config::createDefault();
        $this->holystoneConfig->set('HTML.AllowedElements', implode(',', Config::get('holystone::config.elements')));
        //$this->holystoneConfig->set('HTML.TidyLevel', 'medium');
        $this->holystoneConfig->set('HTML.AllowedAttributes', implode(',', Config::get('holystone::config.attributes')));
        $this->holystoneConfig->set('AutoFormat.AutoParagraph', Config::get('holystone::config.autoParagraph'));
        $this->holystoneConfig->set('HTML.Nofollow', Config::get('holystone::config.nofollow'));
        $this->holystoneConfig->set('AutoFormat.Linkify', Config::get('holystone::config.linkify'));
        $this->htmlPurifier = new \HTMLPurifier($this->holystoneConfig);
    }

    /**
     * Sanitize the HTML from a string
     *
     * @var $html
     * @return string
     */
    public function sanitize($html = '') {
        //$html = $this->markdownParser->text($html);
        $html = $this->htmlPurifier->purify($html);

        return $html;
    }

    protected function assignHTMLPurifierConfig($config) {
        //
    }

}