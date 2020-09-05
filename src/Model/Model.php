<?php
declare(strict_types=1);

namespace MixerApi\Core\Model;

use Cake\Database\Schema\TableSchemaInterface;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;

class Model
{
    /**
     * @var \Cake\Database\Schema\TableSchemaInterface
     */
    private $schema;

    /**
     * @var \Cake\ORM\Table
     */
    private $table;

    /**
     * @var \Cake\Datasource\EntityInterface
     */
    private $entity;

    /**
     * @var \MixerApi\Core\Model\ModelProperty[]
     */
    private $properties = [];

    /**
     * @param \Cake\Database\Schema\TableSchemaInterface $schema cake TableSchema instance
     * @param \Cake\ORM\Table $table cake Table instance
     * @param \Cake\Datasource\EntityInterface $entity cake Entity instance
     */
    public function __construct(
        TableSchemaInterface $schema,
        Table $table,
        EntityInterface $entity
    ) {
        $this->schema = $schema;
        $this->table = $table;
        $this->entity = $entity;
        $this->assignProperties();
    }

    /**
     * @param string $columnName the tables column name
     * @return \MixerApi\Core\Model\ModelProperty
     */
    public function getProperty(string $columnName): ModelProperty
    {
        if (!isset($this->properties[$columnName])) {
            $schema = $this->schema->name();
            throw new \InvalidArgumentException("Column `$columnName` not found in `$schema`");
        }

        return $this->properties[$columnName];
    }

    /**
     * @return void
     */
    private function assignProperties(): void
    {
        $hiddenAttributes = $this->entity->getHidden();

        $columns = array_filter($this->schema->columns(), function ($column) use ($hiddenAttributes) {
            return !in_array($column, $hiddenAttributes) ? true : null;
        });

        foreach ($columns as $columnName) {
            $modelProperty = (new ModelPropertyFactory($this->schema, $this->table, $columnName))->create();

            $this->properties[$columnName] = $modelProperty;
        }
    }

    /**
     * @return \Cake\Database\Schema\TableSchemaInterface
     */
    public function getSchema(): TableSchemaInterface
    {
        return $this->schema;
    }

    /**
     * @return \Cake\ORM\Table
     */
    public function getTable(): Table
    {
        return $this->table;
    }

    /**
     * @return \Cake\Datasource\EntityInterface
     */
    public function getEntity(): EntityInterface
    {
        return $this->entity;
    }

    /**
     * @return \MixerApi\Core\Model\ModelProperty[]
     */
    public function getProperties(): array
    {
        return $this->properties;
    }
}
