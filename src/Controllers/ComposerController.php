<?php declare(strict_types = 1);
namespace SpawnComposerRepository\Controllers;


use Exception;
use SpawnCore\System\CardinalSystem\Request;
use SpawnCore\System\Custom\FoundationStorage\AbstractController;
use SpawnCore\System\Custom\Response\AbstractResponse;
use SpawnCore\System\Custom\Response\CacheControlState;
use SpawnCore\System\Custom\Response\JsonResponse;
use SpawnCore\System\Custom\Response\SimpleResponse;

class ComposerController extends AbstractController {


    /**
     * @route /composer/repository/packages.json
     * @return AbstractResponse
     */
    public function packagesAction(): AbstractResponse {

        $data = [];


        return new JsonResponse($data, new CacheControlState(false, true, true, 10));
    }


    /**
     * @route /composer/repository/webhook
     * @return AbstractResponse
     * @throws Exception
     */
    public function webhookAction(): AbstractResponse {
        /** @var Request $request */
        $request = $this->container->get('system.kernel.request');

        $file = ROOT.'/var/log/webhook_log.txt';
        if(!file_exists(dirname($file))) {
            mkdir(dirname($file));
        }

        file_put_contents($file, 'REQUEST: ' . $request->getRequestURI());
        file_put_contents($file, PHP_EOL.'TIME: ' . (new \DateTime())->format('d.m.Y h:i:s'), FILE_APPEND);
        file_put_contents($file, PHP_EOL.'POST:'.PHP_EOL, FILE_APPEND);
        file_put_contents($file, var_export($request->getPost()->getArray(), true), FILE_APPEND);
        file_put_contents($file, PHP_EOL.'GET:'.PHP_EOL, FILE_APPEND);
        file_put_contents($file, var_export($request->getGet()->getArray(), true), FILE_APPEND);
        file_put_contents($file, PHP_EOL.'COOKIES:'.PHP_EOL, FILE_APPEND);
        file_put_contents($file, var_export($request->getCookies()->getArray(), true), FILE_APPEND);
        file_put_contents($file, PHP_EOL.'PHP stdin:'.PHP_EOL, FILE_APPEND);
        file_put_contents($file, file_get_contents('php://input'), FILE_APPEND);
        file_put_contents($file, PHP_EOL.str_repeat('-', 50).PHP_EOL, FILE_APPEND);

        return new SimpleResponse('');
    }

}
