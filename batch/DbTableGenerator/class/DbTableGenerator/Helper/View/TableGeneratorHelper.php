<?php
/**
 * @package      DbTableGenerator
 * @subpackage   Helper\View
 */

namespace DbTableGenerator\Helper\View;

/**
 * TableGeneratorHelper class
 *
 * @package    DbTableGenerator
 * @subpackage Helper\View
 */
class TableGeneratorHelper {

	/**
	 * Returns the specified table name formatted to a class name.
	 *
	 * @param $tableName
	 *
	 * @return string
	 */
	public function getClassNameFromTableName($tableName) {
		$nameParts = explode('_', $tableName);
		foreach ($nameParts as $key => $value) {
			if (empty($value)) {
				unset($nameParts[$key]);
			}
			$nameParts[$key] = ucfirst(strtolower($value));
		}
		return implode('', $nameParts);
	}

	/**
	 * Returns a the field name formatted to be a field constant.
	 *
	 * @param string $fieldName
	 *
	 * @return string
	 */
	public function getFieldConstant($fieldName) {
		return 'FIELD_' . strtoupper($fieldName);
	}

	/**
	 * Returns the specified enum value as an enum constant for the specified field.
	 *
	 * @param string $fieldName
	 * @param string $enumValue
	 *
	 * @return string
	 */
	public function getEnumConstant($fieldName, $enumValue) {
		return strtoupper($fieldName) . '_' . strtoupper(preg_replace('/[^_0-9a-zA-Z]/', '_', $enumValue));
	}
}