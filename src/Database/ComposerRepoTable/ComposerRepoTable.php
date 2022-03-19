<?php

namespace SpawnComposerRepository\Database\ComposerRepoTable;

use SpawnCore\System\Database\Entity\TableDefinition\AbstractTable;
use SpawnCore\System\Database\Entity\TableDefinition\DefaultColumns\BooleanColumn;
use SpawnCore\System\Database\Entity\TableDefinition\DefaultColumns\CreatedAtColumn;
use SpawnCore\System\Database\Entity\TableDefinition\DefaultColumns\DateTimeColumn;
use SpawnCore\System\Database\Entity\TableDefinition\DefaultColumns\StringColumn;
use SpawnCore\System\Database\Entity\TableDefinition\DefaultColumns\UpdatedAtColumn;
use SpawnCore\System\Database\Entity\TableDefinition\DefaultColumns\UuidColumn;

class ComposerRepoTable extends AbstractTable {

    public const TABLE_NAME = 'spawn_composer_repository';

    public function getTableColumns(): array
    {
        return [
            new UuidColumn('id', null),
            new StringColumn('name', false, '', true),
            new StringColumn('data', false, '[]', false, 750),
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