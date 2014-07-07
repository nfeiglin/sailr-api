<?php

namespace Sailr\Entity;


class RedirectEntity {

    /**
     * @var string $url
     * @var string $type The variable name to be passed with in the 'with' in the redirect request
     * @var string $message The message
     * @var mixed $data Arbitrary data that can be held and accessed in the entity
     */

    public $url = '';
    public $type = 'message';
    public $message = '';
    public $data;

    public function with($type, $message, $url = null) {
        $this->type = $type;
        $this->message = $message;
        if (isset($url)) {
            $this->url = $url;
        }
    }

    public function withMessage($message, $url = null) {
        
        $this->type = 'message';
        $this->message = $message;
        if (isset($url)) {
            $this->url = $url;
        }
    }

    public function getUrl() {
        if (!isset($this->url)) {
            return \URL::to('/');
        }
    }
} 