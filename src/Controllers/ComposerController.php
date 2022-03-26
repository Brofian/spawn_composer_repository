<?php declare(strict_types = 1);
namespace SpawnComposerRepository\Controllers;


use Exception;
use SpawnComposerRepository\Services\ComposerPackageCreator;
use SpawnComposerRepository\Services\ComposerRepositoryService;
use SpawnComposerRepository\Services\GithubWebhookInterpreter;
use SpawnCore\System\CardinalSystem\Request;
use SpawnCore\System\Custom\FoundationStorage\AbstractController;
use SpawnCore\System\Custom\Gadgets\FileEditor;
use SpawnCore\System\Custom\Gadgets\JsonHelper;
use SpawnCore\System\Custom\Response\AbstractResponse;
use SpawnCore\System\Custom\Response\CacheControlState;
use SpawnCore\System\Custom\Response\JsonResponse;
use SpawnCore\System\Custom\Response\SimpleResponse;

class ComposerController extends AbstractController {

    public const WEBHOOK_LOG = ROOT.'/var/log/webhook_log.txt';

    protected ComposerRepositoryService $repositoryService;

    public function __construct(ComposerRepositoryService $repositoryService)   {
        parent::__construct();
        $this->repositoryService = $repositoryService;
    }


    /**
     * @route /composer/repository/packages.json
     * @return AbstractResponse
     */
    public function packagesAction(): AbstractResponse {

        $data = file_get_contents(__DIR__.'/webhook.json');
        $webhookInterpreter = new GithubWebhookInterpreter($data);


        $packageCreator = new ComposerPackageCreator();
        foreach($webhookInterpreter->getBranches() as $branch => $branchData) {
            $packageCreator->addVersionToRepository('spawn/app', 'dev-'.$branch, [
                'source' => [
                    'type' => 'git',
                    'url' => $webhookInterpreter->getRemoteUrl(),
                    'reference' => $branchData['sha']
                ],
                'dist' => [
                    'type' => 'zip',
                    'url' => $webhookInterpreter->getDownloadUrl($branch, false),
                    'reference' => $branchData['sha'],
                    'shasum' => ''
                ],
                'time' => $webhookInterpreter->getTime(),
                'type' => 'library', // TODO: read this automatically from repository
                'description' => '' // TODO: read this automatically from repository
            ]);
        }

        foreach($webhookInterpreter->getTags() as $tag => $tagData) {
            $packageCreator->addVersionToRepository('spawn/app', $tag, [
                'source' => [
                    'type' => 'git',
                    'url' => $webhookInterpreter->getRemoteUrl(),
                    'reference' => $branchData['sha']
                ],
                'dist' => [
                    'type' => 'zip',
                    'url' => $webhookInterpreter->getDownloadUrl($tag, false),
                    'reference' => $branchData['sha'],
                    'shasum' => ''
                ],
                'time' => $webhookInterpreter->getTime(),
                'type' => 'library', // TODO: read this automatically from repository
                'description' => '' // TODO: read this automatically from repository
            ]);
        }

        /*
        dd(
            $webhookInterpreter->getTime(),
            $webhookInterpreter->getBranches(),
            $webhookInterpreter->getTags()
        );
        */

        return new JsonResponse($packageCreator->getDefinition(), new CacheControlState(false, true, true, 10));
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

        $errors = [];
        try {
            $data = file_get_contents('php://input');
            $data = file_get_contents(__DIR__.'/webhook.json');


            $json = JsonHelper::jsonToArray($data);
            $webhookInterpreter = new GithubWebhookInterpreter($data);

            $repository = $this->repositoryService->getRepositoryByName($webhookInterpreter->getRepositoryName());
            if($repository === null) {
                throw new \RuntimeException('Tried updating non existing repository!');
            }



            $packageCreator = new ComposerPackageCreator();
            foreach($webhookInterpreter->getBranches() as $branch => $branchData) {
                $packageCreator->addVersionToRepository('spawn/app', 'dev-'.$branch, [
                    'source' => [
                        'type' => 'git',
                        'url' => $webhookInterpreter->getRemoteUrl(),
                        'reference' => $branchData['sha']
                    ],
                    'dist' => [
                        'type' => 'zip',
                        'url' => $webhookInterpreter->getDownloadUrl($branch, false),
                        'reference' => $branchData['sha'],
                        'shasum' => ''
                    ],
                    'time' => $webhookInterpreter->getTime(),
                    'type' => 'library', // TODO: read this automatically from repository
                    'description' => '' // TODO: read this automatically from repository
                ]);
            }

            foreach($webhookInterpreter->getTags() as $tag => $tagData) {
                $packageCreator->addVersionToRepository('spawn/app', $tag, [
                    'source' => [
                        'type' => 'git',
                        'url' => $webhookInterpreter->getRemoteUrl(),
                        'reference' => $branchData['sha']
                    ],
                    'dist' => [
                        'type' => 'zip',
                        'url' => $webhookInterpreter->getDownloadUrl($tag, false),
                        'reference' => $branchData['sha'],
                        'shasum' => ''
                    ],
                    'time' => $webhookInterpreter->getTime(),
                    'type' => 'library', // TODO: read this automatically from repository
                    'description' => '' // TODO: read this automatically from repository
                ]);
            }

            $repository->setData(JsonHelper::arrayToJson(($packageCreator->getDefinition())));
            if(!$this->repositoryService->upsertRepository($repository)) {
                throw new \RuntimeException('Could not save repository');
            }
        }
        catch (Exception $e) {
            $errors[] = $e->getMessage();
        }

        $response = [
            'success' => empty($errors)
        ];

        if(MODE === 'dev') {
            $response['errors'] = $errors;
        }

        return new JsonResponse($response);
    }

}
