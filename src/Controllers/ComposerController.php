<?php declare(strict_types = 1);
namespace SpawnComposerRepository\Controllers;


use Exception;
use SpawnCore\System\CardinalSystem\Request;
use SpawnCore\System\Custom\FoundationStorage\AbstractController;
use SpawnCore\System\Custom\Gadgets\FileEditor;
use SpawnCore\System\Custom\Response\AbstractResponse;
use SpawnCore\System\Custom\Response\CacheControlState;
use SpawnCore\System\Custom\Response\JsonResponse;
use SpawnCore\System\Custom\Response\SimpleResponse;

class ComposerController extends AbstractController {

    public const WEBHOOK_LOG = ROOT.'/var/log/webhook_log.txt';


    /**
     * @route /composer/repository/packages.json
     * @return AbstractResponse
     */
    public function packagesAction(): AbstractResponse {

        $data = [];


        return new JsonResponse($data, new CacheControlState(false, true, true, 10));
    }


    /**
     * @route /composer/repository/webhook/check
     * @throws Exception
     */
    public function webhookCheckAction(): AbstractResponse {
        /** @var Request $request */
        $request = $this->container->get('system.kernel.request');

        if(!file_exists(dirname(self::WEBHOOK_LOG))) {
            mkdir(dirname(self::WEBHOOK_LOG));
        }

        $eol = PHP_EOL;
        $data = sprintf("REQUEST: %s $eol TIME: %s $eol",
                $request->getRequestURI(),
                (new \DateTime())->format('d.m.Y h:i:s')
        );
        $data .= sprintf("POST:$eol%s$eol GET:$eol%s$eol COOKIES:$eol%s$eol PHP stdin:$eol%s$eol",
            var_export($request->getPost()->getArray(), true),
            var_export($request->getGet()->getArray(), true),
            var_export($request->getCookies()->getArray(), true),
            file_get_contents('php://input')
        );
        $data .= PHP_EOL.str_repeat('-', 50).PHP_EOL;

        FileEditor::append(self::WEBHOOK_LOG, $data);

        return new JsonResponse(['success'=>true]);
    }

    /**
     * @route /composer/repository/webhook
     * @return AbstractResponse
     * @throws Exception
     */
    public function webhookAction(): AbstractResponse {
        /** @var Request $request */
        $request = $this->container->get('system.kernel.request');



        return new SimpleResponse('');
    }

}
