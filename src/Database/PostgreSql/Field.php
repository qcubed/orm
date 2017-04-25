<?php
/**
 *
 * Part of the QCubed PHP framework.
 *
 * @license MIT
 *
 */

namespace QCubed\Database\PostgreSql;

use QCubed\Database\AbstractField;
use QCubed\Database\FieldType;

/**
 * Class QPostgreSqlDatabaseField
 * @package QCubed\Database\PostgreSql
 * @was QPostgreSqlDatabaseField
 */
class Field extends AbstractField {
	/**
	 * QPostgreSqlDatabaseField constructor.
	 *
	 * @param      $mixFieldData
	 * @param null $objDb
	 */
	public function __construct(Row $mixFieldData, $objDb = null) {
		$this->strName = $mixFieldData->GetColumn('column_name');
		$this->strOriginalName = $this->strName;
		$this->strTable = $mixFieldData->GetColumn('table_name');
		$this->strOriginalTable = $this->strTable;
		$this->strDefault = $mixFieldData->GetColumn('column_default');
		$this->intMaxLength = $mixFieldData->GetColumn('character_maximum_length', FieldType::Integer);
		$this->blnNotNull = ($mixFieldData->GetColumn('is_nullable') == "NO") ? true : false;

		// If this column was created as SERIAL and is a simple (non-composite) primary key
		// then we assume it's the identity field.
		// Otherwise, no identity field will be set for this table.
		$this->blnIdentity = false;
		if ($mixFieldData->GetColumn('is_serial') == 't') {
			$objIndexes = $objDb->GetIndexesForTable($this->strTable);
			foreach ($objIndexes as $objIndex) {
				if ($objIndex->PrimaryKey) {
					$columns = $objIndex->ColumnNameArray;
					$this->blnIdentity = (count($columns) == 1 && $columns[0] == $this->strName);
					break;
				}
			}
		}

		// Determine Primary Key
		$objResult = $objDb->Query(sprintf('
				SELECT 
					kcu.column_name 
				FROM 
					information_schema.table_constraints tc, 
					information_schema.key_column_usage kcu 
				WHERE 
					tc.table_name = %s 
				AND 
					tc.table_schema = current_schema() 
				AND 
					tc.constraint_type = \'PRIMARY KEY\' 
				AND 
					kcu.table_name = tc.table_name 
				AND 
					kcu.table_schema = tc.table_schema 
				AND 
					kcu.constraint_name = tc.constraint_name
			', $objDb->SqlVariable($this->strTable)));

		while ($objRow = $objResult->GetNextRow()) {
			if ($objRow->GetColumn('column_name') == $this->strName)
				$this->blnPrimaryKey = true;
		}

		if (!$this->blnPrimaryKey)
			$this->blnPrimaryKey = false;

		// UNIQUE
		$objResult = $objDb->Query(sprintf('
				SELECT 
					kcu.column_name, (SELECT COUNT(*) FROM information_schema.key_column_usage kcu2 WHERE kcu2.constraint_name=kcu.constraint_name ) as unique_fields 
				FROM 
					information_schema.table_constraints tc, 
					information_schema.key_column_usage kcu 
				WHERE 
					tc.table_name = %s 
				AND 
					tc.table_schema = current_schema() 
				AND 
					tc.constraint_type = \'UNIQUE\' 
				AND 
					kcu.table_name = tc.table_name 
				AND 
					kcu.table_schema = tc.table_schema 
				AND 
					kcu.constraint_name = tc.constraint_name
				GROUP BY 
					kcu.constraint_name, kcu.column_name
			', $objDb->SqlVariable($this->strTable)));
		while ($objRow = $objResult->GetNextRow()) {
			if ($objRow->GetColumn('column_name') == $this->strName && $objRow->GetColumn('unique_fields') == '1' )
				$this->blnUnique = true;
		}
		if (!$this->blnUnique)
			$this->blnUnique = false;

		// Determine Type
		$this->strType = $mixFieldData->GetColumn('data_type');

		switch ($this->strType) {
			case 'integer':
			case 'smallint':
			case 'bigint': // 8-byte. PHP int sizes are platform dependent. On 64-bit machines,
				// this is fine. On 32-bit, PHP will convert to float for numbers too big.
				// However, we do NOT want to return a float, as we lose the ability to
				// compare against real integers. (float(0) != int(0))! Assume the developer knows what he
				// is doing if he uses these.
				// http://php.net/manual/en/language.types.integer.php
				$this->strType = FieldType::Integer;

				break;
			case 'money':
				// NOTE: The money type is deprecated in PostgreSQL.
				throw new QPostgreSqlDatabaseException('Unsupported Field Type: money.  Use numeric or decimal instead.', 0,null);
				break;
			case 'decimal':
			case 'numeric':
				// NOTE: PHP's best response to fixed point exact precision numbers is to use the bcmath library.
				// bcmath requires string inputs. If you try to do math directly on these, PHP will convert to float,
				// so for those who care, they will need to be careful. For those who do not care, then PHP will do
				// the conversion automatically.
				$this->strType = FieldType::VarChar;
				break;

			case 'real':
				$this->strType = FieldType::Float;
				break;
			case 'bit':
				if ($this->intMaxLength == 1)
					$this->strType = FieldType::Bit;
				else
					throw new QPostgreSqlDatabaseException('Unsupported Field Type: bit with MaxLength > 1', 0, null);
				break;
			case 'boolean':
				$this->strType = FieldType::Bit;
				break;
			case 'character':
				$this->strType = FieldType::Char;
				break;
			case 'character varying':
			case 'double precision':
				// NOTE: PHP does not offer full support of double-precision floats.
				// Value will be set as a VarChar which will guarantee that the precision will be maintained.
				//    However, you will not be able to support full typing control (e.g. you would
				//    not be able to use a QFloatTextBox -- only a regular QTextBox)
				$this->strType = FieldType::VarChar;
				break;
			case 'json':
			case 'jsonb':
				$this->strType = FieldType::Json;
				break;
			case 'tsvector':
				// this is the TSVector data type in PostgreSQL used for full text search systems.
				// It can safely be used as a text type for displaying the data.
				// NOTE: It must be handled via custom queries.
				// NOTE: It is added here to avoid code generator halting after error because of unrecognized type
				$this->strType = FieldType::VarChar;
				break;
			case 'text':
				$this->strType = FieldType::VarChar;
				break;
			case 'bytea':
				$this->strType = FieldType::Blob;
				break;
			case 'timestamp':
			case 'timestamp with time zone':
				// this data type is not heavily used but is important to be included to avoid errors when code generating.
			case 'timestamp without time zone':
				// System-generated Timestamp values need to be treated as plain text
				$this->strType = FieldType::DateTime; // PostgreSql treats timestamp as a datetime
				//$this->blnTimestamp = true;
				break;
			case 'date':
				$this->strType = FieldType::Date;
				break;
			case 'time':
			case 'time without time zone':
				$this->strType = FieldType::Time;
				break;
			default:
				throw new QPostgreSqlDatabaseException('Unsupported Field Type: ' . $this->strType, 0, null);
		}

		// Retrieve comment
		$this->strComment = $mixFieldData->GetColumn('comment');
	}
}