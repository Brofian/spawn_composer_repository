<?php declare(strict_types = 1);
namespace SpawnComposerRepository\Controllers;


use Doctrine\DBAL\Exception;
use SpawnComposerRepository\Services\ComposerProjectService;
use SpawnComposerRepository\Services\ComposerRepositoryService;
use SpawnCore\System\CardinalSystem\Request;
use SpawnCore\System\Custom\FoundationStorage\AbstractBackendController;
use SpawnCore\System\Custom\Gadgets\FileEditor;
use SpawnCore\System\Custom\Gadgets\UUID;
use SpawnCore\System\Custom\Response\AbstractResponse;
use SpawnCore\System\Custom\Response\CacheControlState;
use SpawnCore\System\Custom\Response\JsonResponse;
use SpawnCore\System\Custom\Response\SimpleResponse;
use SpawnCore\System\Custom\Response\TwigResponse;
use SpawnCore\System\Custom\Throwables\DatabaseConnectionException;
use SpawnCore\System\Custom\Throwables\SubscribeToNotAnEventException;
use SpawnCore\System\Database\Criteria\Criteria;
use SpawnCore\System\Database\Criteria\Filters\EqualsFilter;
use SpawnCore\System\Database\Entity\EntityCollection;
use SpawnCore\System\Database\Entity\RepositoryException;

class ComposerBackendController extends AbstractBackendController {

    protected ComposerProjectService $composerProjectService;
    protected ComposerRepositoryService $composerRepositoryService;
    protected Request $request;


    public function __construct(
        ComposerProjectService $composerProjectService,
        ComposerRepositoryService $composerRepositoryService,
        Request $request
    )
    {
        parent::__construct();
        $this->composerProjectService = $composerProjectService;
        $this->composerRepositoryService = $composerRepositoryService;
        $this->request = $request;
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
     * @name "composer.backend.index"
     * @locked
     * @throws DatabaseConnectionException
     * @throws RepositoryException
     */
    public function indexAction(): AbstractResponse {
        $get = $this->request->getGet();

        $numberOfEntriesPerPage = (int)($get->get('num', 20) ?? 1);
        $page = max((int)($get->get('page', 1) ?? 1), 1);
        $totalNumberOfEntries = ($this->composerProjectService->getNumberOfProjects() ?? 1);
        $availablePages = (int)ceil($totalNumberOfEntries / $numberOfEntriesPerPage);
        $projects = $this->composerProjectService->getProjects(true, $numberOfEntriesPerPage, ($page-1)*$numberOfEntriesPerPage);

        $this->twig->assignBulk([
            'table_info' => [
                'page' => $page,
                'entriesPerPage' => $numberOfEntriesPerPage,
                'availablePages' => $availablePages
            ],
            'projects' => $projects,
            'content_file' => 'backend/contents/composer/overview/content.html.twig'
        ]);

        return new TwigResponse('backend/index.html.twig');
    }


    /**
     * @route /backend/composer/project/{}
     * @name "composer.backend.project"
     * @locked
     * @throws DatabaseConnectionException
     * @throws RepositoryException
     * @throws Exception
     * @throws SubscribeToNotAnEventException
     */
    public function projectOverviewAction(string $projectId): AbstractResponse {
        $get = $this->request->getGet();

        $numberOfEntriesPerPage = (int)($get->get('num', 20) ?? 1);
        $page = max((int)($get->get('page', 1) ?? 1), 1);
        $project = $this->composerProjectService->getProjectsByCriteria(new Criteria(new EqualsFilter('id', UUID::hexToBytes($projectId))), true, 1)->first();
        /** @var EntityCollection $repositories */
        $repositories = $project->repositories;
        /** @var array $repositoryArray */
        $repositoryArray = $repositories->getArrayRange($numberOfEntriesPerPage, ($page-1)*$numberOfEntriesPerPage);
        $totalNumberOfEntries = max($repositories->count(), 1);
        $availablePages = (int)ceil($totalNumberOfEntries / $numberOfEntriesPerPage);

        $this->twig->assignBulk([
            'table_info' => [
                'page' => $page,
                'entriesPerPage' => $numberOfEntriesPerPage,
                'availablePages' => $availablePages,
                'repositories' => $repositoryArray,
            ],
            'project' => $project,
            'content_file' => 'backend/contents/composer/project_view/content.html.twig'
        ]);

        return new TwigResponse('backend/index.html.twig');
    }


    /**
     * @route /backend/composer/repository/{}
     * @name "composer.backend.project.repository"
     * @locked
     */
    public function repositoryOverviewAction(string $repositoryId): AbstractResponse {
        $repository = $this->composerRepositoryService->getRepositoryById(UUID::hexToBytes($repositoryId));

        $this->twig->assign('repository', $repository);
        $this->twig->assign('content_file', 'backend/contents/composer/repository_view/content.html.twig');
        return new TwigResponse('backend/index.html.twig');
    }


    /**
     * @route /backend/composer/webhook/check
     * @name "composer.backend.webhook.check"
     * @locked
     */
    public function webhookCheckAction(): AbstractResponse {

        $checkData = '';
        if(file_exists(ComposerController::WEBHOOK_LOG)) {
            $checkData = FileEditor::getFileContent(ComposerController::WEBHOOK_LOG);
        }

        $this->twig->assign('check_data', $checkData);
        $this->twig->assign('content_file', 'backend/contents/composer/webhook_check/content.html.twig');
        return new TwigResponse('backend/index.html.twig');
    }

    /**
     * @route /backend/composer/repository/create
     * @name "composer.backend.repository.create"
     * @api
     * @locked
     * @return AbstractResponse
     */
    public function createRepositoryAction(): AbstractResponse {
        $postData = $this->request->getPost()->getArray();
        if(!isset($postData['name'])) {
            return new JsonResponse([
                'success' => false,
                'errors' => ['Missing fields in request'],
                'errorFields' => ['name']
            ]);
        }

        $name = $postData['name'];
        $projectId = $postData['projectId'] ?? null;

        $this->composerRepositoryService->createRepository($name, $projectId, $errors);

        return new JsonResponse([
            'success' => empty($errors),
            'errors' => $errors,
            'reload' => true
        ]);
    }


    /**
     * @route /backend/composer/project/create
     * @name "composer.backend.project.create"
     * @api
     * @locked
     * @return AbstractResponse
     */
    public function createProjectAction(): AbstractResponse {
        $postData = $this->request->getPost()->getArray();
        if(!isset($postData['name'])) {
            return new JsonResponse([
                'success' => false,
                'errors' => ['Missing fields in request'],
                'errorFields' => ['name']
            ]);
        }

        $name = $postData['name'];

        $this->composerProjectService->createProject($name, '[]', $errors);

        return new JsonResponse([
            'success' => empty($errors),
            'errors' => $errors,
            'reload' => true
        ]);
    }

}
