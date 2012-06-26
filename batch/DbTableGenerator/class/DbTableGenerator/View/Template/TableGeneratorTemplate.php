<?php
/**
 * @package      DbTableGenerator
 * @subpackage   View\Template
 */

namespace DbTableGenerator\View\Template;

use YapepBase\View\TemplateAbstract;

use DbTableGenerator\Helper\View\TableGeneratorHelper;

/**
 * TableGeneratorTemplate class
 *
 * @package    DbTableGenerator
 * @subpackage View\Template
 */
class TableGeneratorTemplate extends TemplateAbstract {

	/**
	 * Key of the root namespace.
	 *
	 * @var string
	 */
	protected $_rootNamespace;

	/**
	 * Key of the database namespace
	 *
	 * @var string
	 */
	protected $_dbNamespace;

	/**
	 * Key of the table name
	 *
	 * @var string
	 */
	protected $_tableName;

	/**
	 * Key of the fields array
	 *
	 * @var string
	 */
	protected $_fields;

	/**
	 * Key of the enums array
	 *
	 * @var string
	 */
	protected $_enums;

	/**
	 * Constructor
	 *
	 * @param string $_fields
	 * @param string $_enums
	 * @param string $_rootNamespace
	 * @param string $_dbNamespace
	 * @param string $_tableName
	 */
	function __construct($_fields, $_enums, $_rootNamespace, $_dbNamespace, $_tableName) {
		$this->_fields = $_fields;
		$this->_enums = $_enums;
		$this->_rootNamespace = $_rootNamespace;
		$this->_dbNamespace = $_dbNamespace;
		$this->_tableName = $_tableName;
	}

	/**
	 * Does the actual rendering.
	 */
	protected function renderContent() {
		$tableGeneratorHelper = new TableGeneratorHelper();
?>
<?= "<?php\n" ?>
/**
 * @package      <?= $this->get($this->_rootNamespace) . "\n" ?>
 * @subpackage   Dao\Table\<?= $this->get($this->_dbNamespace) . "\n" ?>
 */

namespace <?= $this->get($this->_rootNamespace) ?>\Dao\Table\<?= $this->get($this->_dbNamespace) ?>;

/**
 * Table class for the <?= $this->get($this->_tableName) ?> table.
 *
 * @package      <?= $this->get($this->_rootNamespace) . "\n" ?>
 * @subpackage   Dao\Table\<?= $this->get($this->_dbNamespace) . "\n" ?>
 * @todo         Auto-generated table class, review field and enum definition comments.
 */
class <?= $tableGeneratorHelper->getClassNameFromTableName($this->get($this->_tableName)) ?>Table extends \YapepBase\Database\MysqlTable {

<?php foreach ($this->get($this->_fields) as $fieldName => $comment) : ?>
	/** <?= (empty($comment) ? $fieldName . ' field' : $comment) ?> */
	const <?= $tableGeneratorHelper->getFieldConstant($fieldName) ?> = '<?= addcslashes($fieldName, '\'') ?>';

<?php endforeach; ?>
<?php foreach ($this->get($this->_enums) as $fieldName => $enum) : ?>
	// <?= $fieldName ?> enum constants
<?php foreach ($enum as $value) : ?>
	/** <?= $fieldName ?> enum: <?= $value ?> */
	const <?= $tableGeneratorHelper->getEnumConstant($fieldName, $value) ?> = '<?= addcslashes($value, '\'') ?>';
<?php endforeach ?>

<?php endforeach ?>

	/**
	 * The name of the table.
	 *
	 * @var string
	 */
	protected $tableName = '<?= addcslashes($this->get($this->_tableName), '\'') ?>';

	/**
	 * Returns the fields of the described table.
	 *
	 * @return array   The fields of the table.
	 */
	public function getFields() {
		return array(
<?php foreach ($this->get($this->_fields) as $fieldName => $comment) : ?>
			self::<?= $tableGeneratorHelper->getFieldConstant($fieldName) ?>,
<?php endforeach; ?>
		);
	}
}
<?php
	}

}