<?php declare(strict_types = 1);
namespace SpawnComposerRepository\Controllers;


use SpawnComposerRepository\Services\ComposerProjectService;
use SpawnCore\System\Custom\FoundationStorage\AbstractBackendController;
use SpawnCore\System\Custom\Response\AbstractResponse;
use SpawnCore\System\Custom\Response\CacheControlState;
use SpawnCore\System\Custom\Response\JsonResponse;
use SpawnCore\System\Custom\Response\SimpleResponse;
use SpawnCore\System\Custom\Response\TwigResponse;

class ComposerBackendController extends AbstractBackendController {

    protected ComposerProjectService $composerProjectService;

    public function __construct(
        ComposerProjectService $composerProjectService
    )
    {
        parent::__construct();
        $this->composerProjectService = $composerProjectService;
    }


    public static function getSidebarMethods(): array
    {
        return [
            'composer' => [
                'title' => "composer",
                'color' => "#914b26",
                'actions' => [
                    [
                        'controller' => '%self.key%',
                        'action' => 'indexAction',
                        'title' => 'overview'
                    ]
                ]
            ]
        ];
    }



    /**
     * @route /backend/composer
     * @locked
     * @return AbstractResponse
     */
    public function indexAction(): AbstractResponse {

        $project = $this->composerProjectService->getProjects(true);

        $this->twig->assign('content_file', 'backend/contents/composer/overview.html.twig');
        return new TwigResponse('backend/index.html.twig');
    }

}
