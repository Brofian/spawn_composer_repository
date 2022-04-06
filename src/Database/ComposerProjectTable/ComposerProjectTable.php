<?php declare(strict_types = 1);
namespace SpawnComposerRepository\Database\ComposerProjectTable;

use SpawnCore\System\Database\Entity\TableDefinition\AbstractTable;
use SpawnCore\System\Database\Entity\TableDefinition\DefaultColumns\BooleanColumn;
use SpawnCore\System\Database\Entity\TableDefinition\DefaultColumns\CreatedAtColumn;
use SpawnCore\System\Database\Entity\TableDefinition\DefaultColumns\DateTimeColumn;
use SpawnCore\System\Database\Entity\TableDefinition\DefaultColumns\StringColumn;
use SpawnCore\System\Database\Entity\TableDefinition\DefaultColumns\UpdatedAtColumn;
use SpawnCore\System\Database\Entity\TableDefinition\DefaultColumns\UuidColumn;

class ComposerProjectTable extends AbstractTable {

    public const TABLE_NAME = 'spawn_composer_project';

    public function getTableColumns(): array
    {
        return [
            new UuidColumn('id', null),
            new StringColumn('name', false, '', true, 750),
            new StringColumn('data', false, '[]', false, 750),
            new UpdatedAtColumn(),
            new CreatedAtColumn()
        ];
    }

    public function getTableName(): string
    {
        return self::TABLE_NAME;
    }
}