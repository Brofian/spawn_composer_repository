<?php declare(strict_types = 1);
namespace SpawnComposerRepository\Services;


use Exception;
use SpawnComposerRepository\Database\ComposerProjectTable\ComposerProjectRepository;
use SpawnComposerRepository\Database\ComposerRepoTable\ComposerRepoEntity;
use SpawnComposerRepository\Database\ComposerRepoTable\ComposerRepoRepository;
use SpawnCore\System\Custom\Gadgets\UUID;
use SpawnCore\System\Custom\Throwables\DatabaseConnectionException;
use SpawnCore\System\Custom\Throwables\WrongEntityForRepositoryException;
use SpawnCore\System\Database\Criteria\Criteria;
use SpawnCore\System\Database\Criteria\Filters\EqualsFilter;
use SpawnCore\System\Database\Entity\EntityCollection;
use SpawnCore\System\Database\Entity\RepositoryException;

class ComposerRepositoryService {

    protected ComposerRepoRepository $composerRepository;

    public function __construct(
        ComposerRepoRepository $composerRepository
    )
    {
        $this->composerRepository = $composerRepository;
    }

    public function getRepositoriesByProjectId(string $projectId): EntityCollection {
        return $this->getRepositoriesByCriteria(new Criteria(new EqualsFilter('projectId', UUID::hexToBytes($projectId))));
    }

    public function getRepositoryByName(string $name): ?ComposerRepoEntity {
        try {
            $collection = $this->getRepositoriesByCriteria(new Criteria(new EqualsFilter('name', $name)));
            return $collection->first();
        } catch (Exception $e) {
            return null;
        }
    }

    public function getRepositoryById(string $repositoryId): ?ComposerRepoEntity {
        try {
            $collection = $this->getRepositoriesByCriteria(new Criteria(new EqualsFilter('id', $repositoryId)));
            return $collection->first();
        } catch (Exception $e) {
            return null;
        }
    }


    public function getRepositoriesByCriteria(Criteria $criteria): EntityCollection {
        return $this->composerRepository->search($criteria);
    }


    public function createRepository(string $name, ?string $projectId = null, ?array &$errors = null): ?ComposerRepoEntity {
        $errors = [];

        $entity = new ComposerRepoEntity($name, '{}', $projectId);

        try {
            $this->composerRepository->upsert($entity);
            return $entity;
        }
        catch (Exception $e) {
            $errors[] = $e->getMessage();
            return null;
        }
    }


    public function upsertRepository(ComposerRepoEntity $repository): bool {
        return $this->composerRepository->upsert($repository);
    }
}