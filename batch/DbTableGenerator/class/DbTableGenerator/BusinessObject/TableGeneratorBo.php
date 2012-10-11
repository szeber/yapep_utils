<?php
/**
 * @package      DbTableGenerator
 * @subpackage   BusinessObject
 */

namespace DbTableGenerator\BusinessObject;

use YapepBase\Application;
use YapepBase\Exception\Exception;

/**
 * TableGeneratorBo class
 *
 * @package    DbTableGenerator
 * @subpackage BusinessObject
 */
class TableGeneratorBo {

	/**
	 * Creates the structure for the specified table
	 *
	 * @param string $connectionName   The name of the DB connection to use.
	 * @param string $dbName           The name of the database.
	 * @param string $tableName        The name of the table.
	 * @param array  $enums            Array containing any enum values. (Outgoing param)
	 * @param array  $fields           Array with the field names as keys, and comments as values. (Outgoing param)
	 * @param string $tableComment     The comment for the table will be populated here. (Outgoing param)
	 *
	 * @return void
	 */
	public function getTableStructure(
		$connectionName, $dbName, $tableName, array &$enums, array &$fields = array(), &$tableComment = null
	) {
		$dao = $this->getTableGeneratorDao();
		$tableComment = $dao->getTableComment($connectionName, $dbName, $tableName);
		$structure    = $dao->getTableStructure($connectionName, $dbName, $tableName);
		$fields       = array();
		$enums        = array();
		foreach ($structure as $field) {
			$fields[$field['COLUMN_NAME']] = $field['COLUMN_COMMENT'];
			if ($field['DATA_TYPE'] == 'enum' || $field['DATA_TYPE'] == 'set') {
				$enums[$field['COLUMN_NAME']] = $this->parseEnum($field['COLUMN_TYPE']);
			}
		}
	}

	/**
	 * Returns the parsed values from the specified column type
	 *
	 * @param string $columnType   The column type string
	 *
	 * @return array   The array containing the values of the enums.
	 *
	 * @throws \YapepBase\Exception\Exception
	 *
	 * @todo Do real parsing, because the ',' character as a value is going to be a problem [szeber]
	 */
	protected function parseEnum($columnType) {
		$matches = array();
		if (!preg_match('/^(?:enum|set)\((.*)\)$/', $columnType, $matches)) {
			throw new Exception('Unable to parse string');
		}
		$values = explode('\',\'', $matches[1]);
		foreach ($values as $key => $value) {
			$value = trim($value, ' \'');
			if (empty($value)) {
				unset ($values[$key]);
			} else {
				$values[$key] = $value;
			}
		}
		return $values;
	}

	/**
	 * Returns a TableGeneratorDao instance.
	 *
	 * @return \DbTableGenerator\Dao\TableGeneratorDao
	 */
	protected function getTableGeneratorDao() {
		return Application::getInstance()->getDiContainer()->getDao('TableGenerator');
	}
}