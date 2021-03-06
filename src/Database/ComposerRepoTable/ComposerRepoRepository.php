<?php declare(strict_types = 1);
namespace SpawnComposerRepository\Database\ComposerRepoTable;

use DateTime;
use Exception;
use SpawnCore\System\Custom\Gadgets\UUID;
use SpawnCore\System\Database\Entity\Entity;
use SpawnCore\System\Database\Entity\TableDefinition\AbstractTable;
use SpawnCore\System\Database\Entity\TableRepository;

class ComposerRepoRepository extends TableRepository {

    public function __construct(AbstractTable $tableDefinition)
    {
        parent::__construct($tableDefinition);
    }


    public static function getEntityClass(): string
    {
        return ComposerRepoEntity::class;
    }

    protected function getUpdateFilterColumnsFromValues(array $updateValues): array
    {
        return [
            'id' => UUID::hexToBytes($updateValues['id'])
        ];
    }

    protected function prepareValuesForUpdate(array $updateValues): array
    {
        $updateValues['id'] = UUID::hexToBytes($updateValues['id']);
        $updateValues['projectId'] = $updateValues['projectId'] ? UUID::hexToBytes($updateValues['projectId']) : null;
        $updateValues['updatedAt'] = new DateTime();

        return $updateValues;
    }

    protected function adjustEntityAfterSuccessfulUpdate(Entity $entity, array $updatedValues): void
    {
        /** @var ComposerRepoEntity $entity */
        $entity->setUpdatedAt($updatedValues['updatedAt']);
    }

    /**
     * @param array $values
     * @return array
     * @throws Exception
     */
    protected function prepareValuesForInsert(array $values): array
    {
        $now = new DateTime();

        $values['id'] = UUID::randomBytes();
        $values['projectId'] = $values['projectId'] ? UUID::hexToBytes($values['projectId']) : null;
        $values['createdAt'] = $now;
        $values['updatedAt'] = $now;

        return $values;
    }

    protected function adjustEntityAfterSuccessfulInsert(Entity $entity, array $insertedValues): void
    {
        /** @var ComposerRepoEntity $entity */
        //set the id after the insert command in case of an error
        $entity->setId(UUID::bytesToHex($insertedValues['id']));
        $entity->setProjectId(UUID::bytesToHex($insertedValues['projectId']));
        $entity->setCreatedAt($insertedValues['createdAt']);
        $entity->setUpdatedAt($insertedValues['updatedAt']);
    }



    protected function adjustValuesAfterSelect(array &$values): void
    {
        $values['id'] = UUID::bytesToHex($values['id']);
        $values['projectId'] = UUID::bytesToHex($values['projectId']);
    }
}