<?php namespace Comodojo\DispatcherAssetsLoader;

use \Mimey\MimeTypes;
use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\DispatcherException;
use \Exception;

class AssetsLoader {

    protected $logger;

    protected $path;
    protected $fs;

    protected $content;

    protected $mime;
    protected $mimes;

    protected $supported = [
        "js" => "\\MatthiasMullie\\Minify\\JS",
        "css" => "\\MatthiasMullie\\Minify\\CSS"
    ];

    function __construct($base_path, $vendor, $package, LoggerInterface $logger) {

        $this->logger = $logger;
        $this->mimes = new MimeTypes;

        $this->path = "$base_path/vendor/$vendor/$package/assets";

    }

    public function getContent() {

        return $this->content;

    }

    public function getMime() {

        return $this->mime;

    }

    public function loadFile($type, $name, $minify = false) {

        $file = $this->path."/$name.$type";

        //if ( $this->fs->has($file) ) {

            // $content = $this->fs->read($file);
            // $mime = $this->fs->getMimetype($file);

        if ( file_exists($file) and is_readable($file) ) {

            $content = file_get_contents($file);

            $this->content = ($minify && $this->couldBeMinified($type)) ?
                $this->minify($type, $content) : $content;
            $this->mime = $this->mimes->getMimeType($type);;

        } else {
            throw new DispatcherException("File $file not found!", 0, null, 404);
        }

        return $this;

    }

    public function loadBulk($type, $minify = false) {

        if ( array_key_exists($type, $this->supported) ) {

            $contents = [];

            $path = $this->path."/$type";
            $filter = "$path/*.$type";

            foreach (glob($filter) as $file) {

                $contents[] = file_get_contents($file);

            }

            $this->mime = $this->mimes->getMimeType($type);

            $this->content = $minify ? $this->minify($type, ...$contents) : implode("", $contents);

            return $this;

        }

    }

    protected function couldBeMinified($type) {

        return array_key_exists($type, $this->supported);

    }

    protected function minify($type, ...$contents) {

        $class = $this->supported[$type];

        $minifier = new $class();

        foreach ($contents as $content) {

            $minifier->add($content);

        }

        return $minifier->minify();

    }

}
