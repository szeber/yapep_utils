<?php
/**
 * @package      DbTableGenerator
 * @subpackage   Dao
 */

namespace DbTableGenerator\Dao;

use YapepBase\Database\DbFactory;

/**
 * TableGeneratorDao class
 *
 * @package    DbTableGenerator
 * @subpackage Dao
 */
class TableGeneratorDao {

	/**
	 * Returns the table structure for the specified table.
	 *
	 * @param string $connectionName   The name of the db connection to use.
	 * @param string $dbName           Name of the database.
	 * @param string $tableName        Name of the table.
	 *
	 * @return array|bool
	 */
	public function getTableStructure($connectionName, $dbName, $tableName) {
		$sql = '
			SELECT
				COLUMN_NAME, DATA_TYPE, COLUMN_TYPE, COLUMN_COMMENT
			FROM
				COLUMNS
			WHERE
				TABLE_SCHEMA = :_dbName
				AND TABLE_NAME = :_tableName
			ORDER BY
				ORDINAL_POSITION ASC
		';

		$queryParams = array(
			'dbName'    => $dbName,
			'tableName' => $tableName,
		);

		return DbFactory::getConnection($connectionName, DbFactory::TYPE_READ_ONLY)->query($sql, $queryParams)
			->fetchAll();
	}

	/**
	 * Returns the comment for the specified table.
	 *
	 * @param string $connectionName   The name of the db connection to use.
	 * @param string $dbName           Name of the database.
	 * @param string $tableName        Name of the table.
	 *
	 * @return string
	 */
	public function getTableComment($connectionName, $dbName, $tableName) {
		$sql = '
			SELECT
				TABLE_COMMENT
			FROM
				TABLES
			WHERE
				TABLE_SCHEMA = :_dbName
				AND TABLE_NAME = :_tableName
		';

		$queryParams = array(
			'dbName'    => $dbName,
			'tableName' => $tableName,
		);

		return DbFactory::getConnection($connectionName, DbFactory::TYPE_READ_ONLY)->query($sql, $queryParams)
			->fetchColumn();
	}
}