<?php

namespace Sailr\TestPipe;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\App;


class TestPipe {

    /**
     * @var string $directory
     * @var string $outputHtmlString
     */
    public $directory = '';
    protected $outputHtmlString = '';
    protected $htmlBuilder;
    protected $fileSystem;
    protected $routePrefix = '/build/asset';
    public $config;

    public function __construct(Filesystem $filesystem, array $config) {
        $this->fileSystem = $filesystem;
        $this->config = $config;
    }

    public static function make()  {

        $filesystem = App::make('files');
        $config = Config::get('testpipe.paths');
        $instance = new static($filesystem, $config);
        return $instance;

    }

    public function tags($type = null, $attrs = []) {
        if (isset($type)) {
            return $this->getHtmlTags($this->config[$type], $type, $attrs);
        }
        else {
            return $this->getHtmlTags($this->config);
        }

    }
    protected function getHtmlTags($includePaths = [], $assetType = null, $attrs = []) {

        $this->outputHtmlString = '';

        if (isset($assetType) && $assetType == 'js') {
            $this->outputHtmlString =  $this->compileAllJsTags($includePaths, $attrs);
        }

        else if (isset($assetType) && $assetType == 'css') {
            $this->outputHtmlString = $this->compileAllStyleTags($includePaths, $attrs);
        }

        else {
            $this->outputHtmlString = $this->compileAllStyleTags($includePaths['css'], $attrs) . $this->compileAllJsTags($includePaths['js'], $attrs);
        }

        return $this->outputHtmlString;
    }

    protected function compileAllStyleTags($array, $attrs = []) {
        $string = '';
        foreach($array as $path) {
            $string = $string . $this->generateStyleTag(\URL::action('Sailr\TestPipe\TestPipeController@showAsset', $path),$attrs);
        }
        return $string;
    }

    protected function compileAllJsTags($array, $attrs = []) {
        $string = '';
        foreach($array as $path) {
            $string = $string. $this->buildScriptTag(\URL::action('Sailr\TestPipe\TestPipeController@showAsset', $path), $attrs);
        }

        return $string;
    }

    public function getAsset($path) {
        $directory =  app_path() . '/' . $path;
        $file = $this->fileSystem->get($directory);
        return $file;
    }

    public function getContentType($path) {

        $mime = 'text/plain';
        if (substr($path, -3) == 'css' | substr($path, -4) == 'scss' | substr($path, -4) == 'less') {
            $mime = 'text/css';
        }

        if (substr($path, -2) == 'js') {
            $mime = 'application/javascript';
        }

        if (substr($path, -6) == 'coffee') {
            $mime = 'text/coffeescript';
        }

        return $mime;
    }


    protected function generateStyleTag($url, $attrs = null) {
        $attributesHTML = '';
        if(isset($attrs)) {
            foreach($attrs as $key => $value) {
                $attributesHTML = $attributesHTML . " $key='$value'";
            }
        }

        $openTags = "<link href='$url' type='text/css'";
        $closeTags = ">";

        return $openTags . $attributesHTML . $closeTags;
    }

    protected function buildScriptTag($url, $attrs = null) {

        $attributesHTML = '';
        if(isset($attrs)) {
            foreach($attrs as $key => $value) {
                $attributesHTML = $attributesHTML . " $key='$value'";
            }
        }

        $openTags = "<script src='$url' type='text/javascript'";
        $closeTags = "></script>";

        return $openTags . $attributesHTML . $closeTags;
    }
} 