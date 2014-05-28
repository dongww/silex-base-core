<?php
/**
 * User: dongww
 * Date: 14-5-27
 * Time: 上午9:07
 */

namespace Dongww\Db\Dbal;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Symfony\Component\Yaml\Parser;

class Checker
{
    protected $conn;

    protected static $DATA_TYPE_MAP = [
        'string'   => 'string',
        'text'     => 'text',
        'integer'  => 'integer',
        'float'    => 'float',
        'datetime' => 'datetime',
        'date'     => 'date',
        'time'     => 'time',
    ];

    public function __construct(Connection $conn)
    {
        $this->conn = $conn;
    }

    /**
     * 获得数据库的更改后的 SQL语句 数组
     *
     * @param string $fileName 数据结构的文件名
     * @return array
     */
    public function getDiffSql($fileName)
    {
        $yaml   = new Parser();
        $data   = $yaml->parse(file_get_contents($fileName));
        $tables = [];

        $newSchema = new Schema();
        foreach ($data['tables'] as $tblName => $tbl) {
            $tables[$tblName] = $newSchema->createTable($tblName);
            /** @var \Doctrine\DBAL\Schema\Table $newTable */
            $newTable = $tables[$tblName];

            foreach ($tbl['fields'] as $fieldName => $field) {
                $options            = [];
                $options['notnull'] = isset($field['required']) ? (bool)$field['required'] : false;

                if (isset(self::$DATA_TYPE_MAP[$field['type']])) {
                    $newTable->addColumn($fieldName, self::$DATA_TYPE_MAP[$field['type']], $options);
                }
            }

            $newTable->addColumn("id", "integer", array('autoincrement' => true));
            $newTable->setPrimaryKey(array("id"));

            /** timestamp_able 创建时间，更改时间 */
            $timeAble = isset($tbl['timestamp_able']) ? $tbl['timestamp_able'] : false;
            if ($timeAble) {
                $newTable->addColumn('created_at', "datetime");
                $newTable->addColumn('updated_at', "datetime");
            }

            /** tree_able 可进行树状存储 */
            $treeAble = isset($tbl['tree_able']) ? $tbl['tree_able'] : false;
            if ($treeAble) {
                $newTable->addColumn("sort", "integer", ['notnull' => false]);
                $newTable->addColumn("path", "string", ['notnull' => false]);
                $newTable->addColumn("level", "integer", ['notnull' => false]);

                $newTable->addColumn("parent_id", "integer", ['notnull' => false]);
                $newTable->addForeignKeyConstraint(
                    $newTable,
                    array('parent_id'),
                    array("id"),
                    array("onUpdate" => "CASCADE")
                );
            }
        }

        /** 多对一 */
        foreach ($data['tables'] as $tblName => $tbl) {
            if (isset($tbl['parents'])) {
                foreach ($tbl['parents'] as $p) {
                    $this->addForeign($tables[$tblName], $tables[$p]);
                }
            }
        }

        /** 多对多 */
        if (is_array($data['many_many'])) {
            foreach ($data['many_many'] as $mm) {
                $tblName          = $mm[0] . '_' . $mm[1];
                $tables[$tblName] = $newSchema->createTable($tblName);

                $this->addForeign($tables[$tblName], $tables[$mm[0]]);
                $this->addForeign($tables[$tblName], $tables[$mm[1]]);
            }
        }

        $oldSchema = $this->conn->getSchemaManager()->createSchema();

        return $sql = $oldSchema->getMigrateToSql($newSchema, $this->conn->getDatabasePlatform());
    }

    public function addForeign(Table $table, Table $foreignTable)
    {
        $columnName = $foreignTable->getName() . '_id';
        $table->addColumn($columnName, "integer");
        $table->addForeignKeyConstraint(
            $foreignTable,
            array($columnName),
            array("id"),
            array("onUpdate" => "CASCADE")
        );
    }
}
