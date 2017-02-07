<?php namespace Comodojo\DispatcherAssetsLoader\Service;

use \Comodojo\Dispatcher\Service\AbstractService;
use \Comodojo\DispatcherAssetsLoader\AssetsLoader;
use \Comodojo\Exception\DispatcherException;
use \Exception;

class GetAssets extends AbstractService {

    public function get() {

        // framework components
        $query = $this->request->query;
        $base_path = $this->configuration->get('base-path');
        $logger = $this->logger;

        // required parts
        $vendor = $query->get('vendor');
        $package = $query->get('package');

        // optional parts
        $type = $query->get('type');
        $filename = $query->get('filename');
        $minify = $query->get('minify') == 'yes' ? true : false;

        try {

            $loader = new AssetsLoader($base_path, $vendor, $package, $logger);

            if ( $type !== null ) {

                $loader->loadBulk($type[1], $minify);

            } else if ( $filename === null ) {

                throw new DispatcherException("Filename not specified", 0, null, 404);

            } else {

                $loader->loadFile(
                    $filename[2], // File extension
                    $filename[1],  // File name
                    $minify
                );

            }

        } catch (DispatcherException $de) {

            throw $de;

        } catch (Exception $e) {

            throw new DispatcherException($e->getMessage(), 0, $e, 500);

        }

        $this->response->content->type($loader->getMime());

        return $loader->getContent();

    }

}
