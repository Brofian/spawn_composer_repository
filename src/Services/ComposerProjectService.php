<?php declare(strict_types = 1);
namespace SpawnComposerRepository\Services;


use Exception;
use SpawnComposerRepository\Database\ComposerProjectTable\ComposerProjectEntity;
use SpawnComposerRepository\Database\ComposerProjectTable\ComposerProjectRepository;
use SpawnComposerRepository\Database\ComposerProjectTable\ComposerProjectTable;
use SpawnComposerRepository\Database\ComposerRepoTable\ComposerRepoEntity;
use SpawnComposerRepository\Database\ComposerRepoTable\ComposerRepoRepository;
use SpawnComposerRepository\Database\ComposerRepoTable\ComposerRepoTable;
use SpawnCore\System\Custom\Gadgets\JsonHelper;
use SpawnCore\System\Custom\Gadgets\UUID;
use SpawnCore\System\Custom\Response\Exceptions\JsonConvertionException;
use SpawnCore\System\Custom\Throwables\DatabaseConnectionException;
use SpawnCore\System\Database\Criteria\Criteria;
use SpawnCore\System\Database\Criteria\Filters\EqualsFilter;
use SpawnCore\System\Database\Criteria\Filters\InFilter;
use SpawnCore\System\Database\Criteria\Relation\Relation;
use SpawnCore\System\Database\Entity\EntityCollection;
use SpawnCore\System\Database\Entity\RepositoryException;
use SpawnCore\System\ServiceSystem\ServiceContainerProvider;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;

class ComposerProjectService {

    protected ComposerProjectRepository $composerProjectRepository;

    public function __construct(
        ComposerProjectRepository $composerProjectRepository
    )
    {
        $this->composerProjectRepository = $composerProjectRepository;
    }

    public function getProjectByName(string $name, bool $includeRepositories = false): ?ComposerProjectEntity {
        try {
            $collection = $this->getProjectsByCriteria(new Criteria(new EqualsFilter('name', $name)), $includeRepositories);
            return $collection->first();
        } catch (Exception $e) {
            return null;
        }
    }

    public function getProjects(bool $includeRepositories = false): EntityCollection {
        return $this->getProjectsByCriteria(new Criteria(), $includeRepositories);
    }

    public function getProjectsByCriteria(Criteria $criteria, bool $includeRepositories): EntityCollection {
        $projects = $this->composerProjectRepository->search($criteria);


        if($includeRepositories) {
            /** @var ComposerRepositoryService $repositoryService */
            $repositoryService = ServiceContainerProvider::getServiceContainer()->get('composer_repository.service.composerRepositoryService');

            /** @var ComposerProjectEntity $project */
            foreach($projects as $project) {
                $repositories = $repositoryService->getRepositoriesByProjectId($project->getId());
                $project->set('repositories', $repositories);
            }
        }


        return $projects;
    }

    public function createProject(string $name, string $data): ?ComposerProjectEntity {
        if(!JsonHelper::validateJson($data)) {
            return null;
        }

        $entity = new ComposerProjectEntity($name,$data);

        try {
            $this->composerProjectRepository->upsert($entity);
        }
        catch (Exception $e) {
            return null;
        }

        return $entity;
    }



}