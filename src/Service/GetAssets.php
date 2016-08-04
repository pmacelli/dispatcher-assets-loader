<?php namespace Comodojo\DispatcherAssetsLoader\Service;

use \Comodojo\Dispatcher\Service\AbstractService;
use \Comodojo\DispatcherAssetsLoader\AssetsLoader;
use \Comodojo\Exception\DispatcherException;
use \Exception;

class GetAssets extends AbstractService {

    public function get() {

        $params = $this->request->query->get();

        try {

            $loader = new AssetsLoader($params['vendor'], $params['package']);

            if (isset($params['type'])) {

                $loader->loadMinifiedFiles($params['type'][1]);

            } else {

                if (!isset($params['filename'])) {

                    throw new DispatcherException("You must specify a filename!", 0, null, 404);

                }

                $loader->loadFile(
                    $params['filename'][2], // File extension
                    $params['filename'][1]  // File name
                );

            }

        } catch (DispatcherException $de) {

            throw $de;

        } catch (Exception $e) {

            throw new DispatcherException($e->getMessage(), 0, null, 500);

        }

        $this->response->headers->set("Content-Type", $loader->getLoadedMimeType());

        $this->response->headers->set("Content-Length", $loader->getLoadedContentSize());

        return $loader->getLoadedContent();

    }

}
