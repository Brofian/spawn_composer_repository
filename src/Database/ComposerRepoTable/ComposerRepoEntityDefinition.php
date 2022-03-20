<?php declare(strict_types = 1);
namespace SpawnComposerRepository\Database\ComposerRepoTable;


use DateTime;
use SpawnCore\System\Custom\Gadgets\JsonHelper;
use SpawnCore\System\Database\Entity\Entity;
use SpawnCore\System\Database\Entity\EntityTraits\EntityCreatedAtTrait;
use SpawnCore\System\Database\Entity\EntityTraits\EntityIDTrait;
use SpawnCore\System\Database\Entity\EntityTraits\EntityUpdatedAtTrait;

class ComposerRepoEntityDefinition extends Entity
{
    use EntityIDTrait;
    use EntityUpdatedAtTrait;
    use EntityCreatedAtTrait;

    protected ?string $projectId = null;
    protected string $name;
    protected string $data;
    protected bool $active;

    public function __construct(
        string $name,
        string $data,
        ?string $projectId = null,
        bool $active = false,
        ?string $id = null,
        ?DateTime $createdAt = null,
        ?DateTime $updatedAt = null
    )
    {
        $this->setProjectId($projectId);
        $this->setName($name);
        $this->setData($data);
        $this->setActive($active);
        $this->setId($id);
        $this->setUpdatedAt($updatedAt);
        $this->setCreatedAt($createdAt);
    }


    public function getRepositoryClass(): string
    {
        return ComposerRepoRepository::class;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'projectId' => $this->getProjectId(),
            'name' => $this->getName(),
            'data' => $this->getData(),
            'active' => $this->isActive(),
            'updatedAt' => $this->getUpdatedAt(),
            'createdAt' => $this->getCreatedAt(),
        ];
    }

    public static function getEntityFromArray(array $values): Entity
    {
        $values['updatedAt'] = self::getDateTimeFromVariable($values['updatedAt'] ?? null);
        $values['createdAt'] = self::getDateTimeFromVariable($values['createdAt'] ?? null);

        return new ComposerRepoEntity(
            $values['name'],
            $values['data'],
            $values['projectId'] ?? null,
            (bool)($values['active'] ?? false),
            $values['id'] ?? null,
            $values['createdAt'] ?? null,
            $values['updatedAt'] ?? null
        );
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getDataArray(): array
    {
        return JsonHelper::jsonToArray($this->data, );
    }


    public function setData(string $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function setDataArray(array $data): self
    {
        $this->data = JsonHelper::arrayToJson($data);
        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    public function getProjectId(): ?string
    {
        return $this->projectId;
    }

    public function setProjectId(?string $projectId): self
    {
        $this->projectId = $projectId;
        return $this;
    }




}