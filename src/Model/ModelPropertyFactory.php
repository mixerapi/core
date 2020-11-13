<?php
declare(strict_types=1);

namespace MixerApi\Core\Model;

use Cake\Database\Schema\TableSchemaInterface;
use Cake\ORM\Table;
use Cake\Validation\Validator;

class ModelPropertyFactory
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
     * @var string
     */
    private $columnName;

    /**
     * @param \Cake\Database\Schema\TableSchemaInterface $schema cake TableSchema instance
     * @param \Cake\ORM\Table $table cake Table instance
     * @param string $columnName the tables column name
     */
    public function __construct(TableSchemaInterface $schema, Table $table, string $columnName)
    {
        $this->schema = $schema;
        $this->table = $table;
        $this->columnName = $columnName;
    }

    /**
     * @return \MixerApi\Core\Model\ModelProperty
     */
    public function create()
    {
        $vars = $this->schema->__debugInfo();
        $default = $vars['columns'][$this->columnName]['default'] ?? '';

        return (new ModelProperty())
            ->setName($this->columnName)
            ->setType($this->schema->getColumnType($this->columnName))
            ->setDefault((string)$default)
            ->setIsPrimaryKey($this->isPrimaryKey($vars, $this->columnName))
            ->setValidationSet($this->table->validationDefault(new Validator())->field($this->columnName));
    }

    /**
     * @param array $schemaDebugInfo debug array from TableSchema
     * @param string $columnName column name
     * @return bool
     */
    private function isPrimaryKey(array $schemaDebugInfo, string $columnName): bool
    {
        if (!isset($schemaDebugInfo['constraints']['primary']['columns'])) {
            return false;
        }

        return in_array($columnName, $schemaDebugInfo['constraints']['primary']['columns']);
    }
}
