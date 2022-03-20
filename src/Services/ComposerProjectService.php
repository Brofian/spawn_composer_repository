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
use SpawnCore\System\Custom\Throwables\SubscribeToNotAnEventException;
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

    public function getProjects(bool $includeRepositories = false, int $limit = 99999, int $offset = 0): EntityCollection {
        return $this->getProjectsByCriteria(new Criteria(), $includeRepositories, $limit, $offset);
    }


    /**
     * @throws DatabaseConnectionException
     * @throws RepositoryException
     * @throws \Doctrine\DBAL\Exception
     * @throws SubscribeToNotAnEventException
     */
    public function getProjectsByCriteria(Criteria $criteria, bool $includeRepositories, int $limit = 99999, int $offset = 0): EntityCollection {
        $projects = $this->composerProjectRepository->search($criteria, $limit, $offset);


        if($includeRepositories) {
            /** @var ComposerRepositoryService $repositoryService */
            $repositoryService = ServiceContainerProvider::getServiceContainer()->get('composer_repository.service.composer_repository_service');

            /** @var ComposerProjectEntity $project */
            foreach($projects as $project) {
                $repositories = $repositoryService->getRepositoriesByProjectId($project->getId());
                $project->set('repositories', $repositories);
            }
        }


        return $projects;
    }

    /**
     * @throws DatabaseConnectionException
     * @throws RepositoryException
     */
    public function getNumberOfProjects(?Criteria $criteria = null): int {
        return $this->composerProjectRepository->count($criteria ?? new Criteria());
    }

    public function createProject(string $name, string $data, ?array &$errors): ?ComposerProjectEntity {
        $errors = [];

        if(!JsonHelper::validateJson($data)) {
            $errors[] = 'Invalid json data: ' . $data;
            return null;
        }

        $entity = new ComposerProjectEntity($name,$data);

        try {
            $this->composerProjectRepository->upsert($entity);
        }
        catch (Exception $e) {
            $errors[] = $e->getMessage();
            return null;
        }

        return $entity;
    }



}