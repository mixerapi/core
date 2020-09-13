<?php
declare(strict_types=1);

namespace MixerApi\Core\Model;

use Cake\Collection\Collection;
use Cake\Datasource\ConnectionInterface;
use Cake\Utility\Inflector;
use MixerApi\Core\Utility\NamespaceUtility;

class ModelFactory
{
    /**
     * @var \Cake\Database\Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @param \Cake\Datasource\ConnectionInterface $connection db connection instance
     * @param string $namespace the App namespace (e.g. App)
     * @param string $tableName the database table name
     */
    public function __construct(ConnectionInterface $connection, string $namespace, string $tableName)
    {
        $this->connection = $connection;
        $this->namespace = $namespace;
        $this->tableName = $tableName;
    }

    /**
     * @return \MixerApi\Core\Model\Model|null
     */
    public function create(): ?Model
    {
        $tableName = $this->findTable();

        if ($tableName === null) {
            return null;
        }

        $tableClass = Inflector::pluralize(Inflector::classify($tableName)) . 'Table';
        $tableFqn = NamespaceUtility::findClass($this->namespace . '\Model\Table', $tableClass);
        /** @var \Cake\ORM\Table $tableInstance */
        $tableInstance = new $tableFqn();
        $entityFqn = $tableInstance->getEntityClass();

        return new Model(
            $this->connection->getSchemaCollection()->describe($tableName),
            $tableInstance,
            new $entityFqn()
        );
    }

    /**
     * @return string|null
     * @throws \RuntimeException
     */
    private function findTable(): ?string
    {
        $tables = (new TableScanner($this->connection))->listUnskipped();

        $collection = new Collection($tables);
        $results = $collection->filter(function ($table) {
            return $table == $this->tableName;
        });

        if ($results->count() === 0) {
            return null;
        }

        return $results->first();
    }
}
