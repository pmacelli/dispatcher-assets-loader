<?php namespace \Bundle;

use \MatthiasMullie\Minify;
use \Comodojo\Exception\DispatcherException;
use \Exception;

class AssetsLoader {
    
    private $path;
    
    private $content = "";
    
    private $mime = "text/plain";
    
    private $supported = array(
        "js" => "js",
        "html" => "html",
        "jst" => "templates",
        "css" => "css"
    );
    
    private $min_supported = array(
        "js" => array(
            "class" => "Minify\\JS",
            "mime" => "text/javascript"
        ),
        "css" => array(
            "class" => "Minify\\CSS",
            "mime" => "text/css"
        )
    );
    
    function __construct($vendor, $package) {
        
        $base_path = dirname(__FILE__, 4);
        
        $this->path = $base_path . "/" . $vendor . "/" . $package . "/assets";
        
        if (!file_exists($this->path)) {
            
            throw new DispatcherException("The package assets for $vendor/$package are not available!", 0, null, 404);
            
        }
        
    }
    
    public function getLoadedContent() {
        
        return $this->content;
        
    }
    
    public function getLoadedContentSize() {
        
        return strlen($this->content);
        
    }
    
    public function getLoadedMimeType() {
        
        return $this->mime;
        
    }
        
    public function getMinifiedFiles($type) {
        
        if (in_array($type, array_keys($this->min_supported))) {
            
            $files = $this->getFilesByType($type);
            
            $class = $this->min_supproted[$type]['class'];
            
            $minifier = new $class();
            
            foreach ($files as $file) {
                
                $minifier->add($file['path']);
                
            }
            
            $this->mime = $this->min_supproted[$type]['mime'];
            
            $this->content = $minifier->minify();
            
            return $this;
            
        }
        
        throw new DispatcherException("This format cannot be minified!", 0, null, 404);
        
    }
    
    public function getFiles($type) {
        
        if (in_array($type, array_keys($this->supported))) {
            
            return $this->getFilesByType($type);
            
        } else {
            
            return $this->getDataFiles($type);
            
        }
        
    }
    
    public function getFile($type, $name) {
        
        $files = $this->getFiles($type);
        
        $filename = $name . "." . $type;
        
        if (isset($files[$filename])) {
            
            if (in_array($type, array_keys($this->min_supported))) {
                
                $this->getMinifiedFile($type, $files[$filename]['path']);
                
            } else {
                
                $this->mime = $files[$filename]['mime'];
                
                $this->content = file_get_contents($files[$filename]['path']);
                
            }
            
        } else {
            
            throw new DispatcherException("File $filename not found!", 0, null, 404);
            
        }
        
        return $this;
        
    }
        
    private function getMinifiedFile($type, $path) {
            
        $class = $this->min_supproted[$type]['class'];
        
        $minifier = new $class($path);
        
        $this->mime = $this->min_supproted[$type]['mime'];
        
        $this->content = $minifier->minify();
        
        return $this;
        
    }
    
    private function getFilesByType($type) {
        
        $list = array();
        
        $path = $this->path . "/" . $this->supported[$type];
        
        if (isset($this->supported[$type]) && file_exists($path)) {
        
            foreach (glob($path . "/*." . $type) as $file) {
                
                $list[basename($file)] = array(
                    "path" => $file,
                    "mime" => mime_content_type ($file)
                );
                
            }
            
        }
        
        return $list;
        
    }
    
    private function getDataFiles($filter) {
        
        $list = array();
        
        $path = $this->path . "/data";
        
        if (file_exists($path)) {
        
            foreach (glob($path . "/*." . $filter) as $file) {
                
                $list[basename($file)] = array(
                    "path" => $file,
                    "mime" => mime_content_type ($file)
                );
                
            }
            
        }
        
        return $list;
        
    }
    
}
