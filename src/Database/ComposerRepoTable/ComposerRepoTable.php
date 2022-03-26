<?php declare(strict_types = 1);
namespace SpawnComposerRepository\Database\ComposerRepoTable;

use SpawnComposerRepository\Database\ComposerProjectTable\ComposerProjectTable;
use SpawnCore\System\Database\Entity\TableDefinition\AbstractTable;
use SpawnCore\System\Database\Entity\TableDefinition\DefaultColumns\BooleanColumn;
use SpawnCore\System\Database\Entity\TableDefinition\DefaultColumns\CreatedAtColumn;
use SpawnCore\System\Database\Entity\TableDefinition\DefaultColumns\DateTimeColumn;
use SpawnCore\System\Database\Entity\TableDefinition\DefaultColumns\StringColumn;
use SpawnCore\System\Database\Entity\TableDefinition\DefaultColumns\UpdatedAtColumn;
use SpawnCore\System\Database\Entity\TableDefinition\DefaultColumns\UuidColumn;
use SpawnCore\System\Database\Entity\TableDefinition\ForeignKey;

class ComposerRepoTable extends AbstractTable {

    public const TABLE_NAME = 'spawn_composer_repository';

    public function getTableColumns(): array
    {
        return [
            new UuidColumn('id', null),
            new UuidColumn('projectId', new ForeignKey(ComposerProjectTable::TABLE_NAME, 'id', true, false)),
            new StringColumn('name', false, '', true, 750),
            new StringColumn('data', false, '[]', false),
            new BooleanColumn('active', false),
            new UpdatedAtColumn(),
            new CreatedAtColumn()
        ];
    }

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }
}