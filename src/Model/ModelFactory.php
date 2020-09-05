<?php
declare(strict_types=1);

namespace MixerApi\Core\Model;

use Cake\Collection\Collection;
use Cake\Datasource\ConnectionInterface;
use Cake\Utility\Inflector;
use MixerApi\Core\Utility\NamespaceUtility;
use RuntimeException;

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
     * @return \MixerApi\Core\Model\Model
     */
    public function create(): Model
    {
        $tableName = $this->findTable();
        $entityClass = Inflector::singularize(Inflector::classify($this->tableName));
        $tableClass = Inflector::pluralize(Inflector::classify($tableName)) . 'Table';

        $entityFqn = NamespaceUtility::findClass($this->namespace . '\Model\Entity', $entityClass);
        $tableFqn = NamespaceUtility::findClass($this->namespace . '\Model\Table', $tableClass);

        return new Model(
            $this->connection->getSchemaCollection()->describe($tableName),
            new $tableFqn(),
            new $entityFqn()
        );
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    private function findTable(): string
    {
        $tables = (new TableScanner($this->connection))->listUnskipped();

        $collection = new Collection($tables);
        $results = $collection->filter(function ($table) {
            return $table == $this->tableName;
        });

        if ($results->count() === 0) {
            throw new RuntimeException('Table not found');
        }

        return $results->first();
    }
}
