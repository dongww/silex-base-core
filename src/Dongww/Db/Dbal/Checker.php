<?php
/**
 * User: dongww
 * Date: 14-5-27
 * Time: 上午9:07
 */

namespace Dongww\Db\Dbal;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\Yaml\Parser;

class Checker
{
    protected $conn;

    function __construct(Connection $conn)
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

            foreach ($tbl['fields'] as $fieldName => $field) {
                $options = [];
                if (isset($field['required'])) {
                    $options['notnull'] = $field['required'] ? true : false;
                } else {
                    $options['notnull'] = false;
                }

                switch ($field['type']) {
                    case 'string':
                        $tables[$tblName]->addColumn($fieldName, "string", $options);
                        break;
                    case 'text':
                        $tables[$tblName]->addColumn($fieldName, "text", $options);
                        break;
                    case 'integer':
                        $tables[$tblName]->addColumn($fieldName, "integer", $options);
                        break;
                    case 'float':
                        $tables[$tblName]->addColumn($fieldName, "float", $options);
                        break;
                    case 'date':
                        $tables[$tblName]->addColumn($fieldName, "date", $options);
                        break;
                    case 'datetime':
                        $tables[$tblName]->addColumn($fieldName, "datetime", $options);
                        break;
                    case 'time':
                        $tables[$tblName]->addColumn($fieldName, "time", $options);
                        break;
                }
            }

            $tables[$tblName]->addColumn("id", "integer", array('autoincrement' => true));
            $tables[$tblName]->setPrimaryKey(array("id"));

            /** time_stampable 创建时间，更改时间 */
            $timeAble = isset($tbl['timestamp_able']) ? $tbl['timestamp_able'] : false;
            if ($timeAble) {
                $tables[$tblName]->addColumn('created_at', "datetime");
                $tables[$tblName]->addColumn('updated_at', "datetime");
            }

            /** tree_able 可进行树状存储 */
            $treeAble = isset($tbl['tree_able']) ? $tbl['tree_able'] : false;
            if ($treeAble) {
                $tables[$tblName]->addColumn("sort", "integer", ['notnull' => false]);
                $tables[$tblName]->addColumn("path", "string", ['notnull' => false]);
                $tables[$tblName]->addColumn("level", "integer", ['notnull' => false]);

                $tables[$tblName]->addColumn("parent_id", "integer", ['notnull' => false]);
                $tables[$tblName]->addForeignKeyConstraint($tables[$tblName], array('parent_id'), array("id"), array("onUpdate" => "CASCADE"));
            }
        }

        /** 多对一 */
        foreach ($data['tables'] as $tblName => $tbl) {
            if (isset($tbl['parents'])) {
                foreach ($tbl['parents'] as $p) {
                    $columnName = $p . '_id';
                    $tables[$tblName]->addColumn($columnName, "integer");
                    $tables[$tblName]->addForeignKeyConstraint($tables[$p], array($columnName), array("id"), array("onUpdate" => "CASCADE"));
                }
            }
        }

        /** 多对多 */
        foreach ($data['many_many'] as $mm) {
            $tblName          = $mm[0] . '_' . $mm[1];
            $tables[$tblName] = $newSchema->createTable($tblName);

            $columnName = $mm[0] . '_id';
            $tables[$tblName]->addColumn($columnName, "integer");
            $tables[$tblName]->addForeignKeyConstraint($tables[$mm[0]], array($columnName), array("id"), array("onUpdate" => "CASCADE"));

            $columnName = $mm[1] . '_id';
            $tables[$tblName]->addColumn($columnName, "integer");
            $tables[$tblName]->addForeignKeyConstraint($tables[$mm[1]], array($columnName), array("id"), array("onUpdate" => "CASCADE"));
        }

        $oldSchema = $this->conn->getSchemaManager()->createSchema();

        return $sql = $oldSchema->getMigrateToSql($newSchema, $this->conn->getDatabasePlatform());
    }
}
 