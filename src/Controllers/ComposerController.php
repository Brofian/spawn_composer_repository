<?php declare(strict_types = 1);
namespace SpawnComposerRepository\Controllers;


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
     */
    public function webhookAction(): AbstractResponse {



        return new SimpleResponse('Helloworld');
    }

}
