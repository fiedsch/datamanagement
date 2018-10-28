<?php /** @noinspection SyntaxError */

/**
 * @package    Datamanagement
 * @author     Andreas Fieger <fiedsch@ja-eh.at>
 * @copyright  2018 Andreas Fieger
 * @license    MIT
 * @link       https://github.com/fiedsch/datamanagement
 */

namespace Fiedsch\Data\Utility;

use Fiedsch\Data\File\Reader;
use Fiedsch\Data\File\CsvReader;

/**
 * Create SQL statements to read data into a database.
 *
 * Currently supports only MySQL!
 *
 * Can be used to create a *.sql file much like mysqldump would do.
 * An alternative would be to use the mysqldump with the --tab option -- see
 * https://dev.mysql.com/doc/refman/8.0/en/reloading-delimited-text-dumps.html
 */
class SqlCodeGenerator
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $inputFile;

    /**
     * @var CsvReader
     */
    protected $reader;

    /**
     * SqlCodeGenerator constructor.
     *
     * @param string $inputFile
     * @param array $config
     * @throws \Exception
     */
    public function __construct($inputFile, $config = [])
    {
        $this->inputFile = $inputFile;
        $this->config = $config;
        $this->assertValidConfig();
        $this->reader = new CsvReader($this->inputFile, ';');
        $this->reader->readHeader();
    }

    /**
     * Destructor.
     * Closes possibly still open reader.
     */
    public function __destruct()
    {
        $this->reader->close();
    }

    /**
     * Asserts that all required config settings are available.
     *
     * @throws \RuntimeException
     */
    protected function assertValidConfig()
    {
        if (!isset($this->config['table'])) {
            throw new \RuntimeException("configuration option 'table' is missing");
        }
        if (!isset($this->config['types']['default'])) {
            throw new \RuntimeException("configuration option 'types.default' is missing");
        }
    }

    /**
     * @return string
     */
    public function getDropTable() {
        return sprintf('DROP TABLE IF EXISTS `%s`;', $this->config['table']);
    }


        /**
     * Returns the CREATE TABLE statement
     *
     * @return string
     * @throws \RuntimeException
     */
    public function getCreateTable()
    {
        /** Having 'CREATE' outside the sprintf() is used to avoid PHPStorm's SQL inspection
         * and reporting an error (which is not there).
         * TODO: find a better was to tell PHPStorm  to shut up.
         */
        $result = 'CREATE '.sprintf('TABLE `%s` (', $this->config['table']);
        $header = $this->reader->getHeader();
        $columndefinitions = [];
        foreach ($header as $columnName) {
            $columndefinitions[] = sprintf('`%s` %s', $columnName, $this->getColumnType($columnName));
        }
        $result .= implode(',', $columndefinitions) .');';
        return $result;
    }

    /**
     * @param string $columnName
     * @return string
     */
    protected function getColumnType($columnName)
    {
        $colName = mb_strtolower($columnName);
        if (!isset($this->config['columns'][$colName])) {
            return $this->config['types']['default'];
        }
        $colType = $this->config['columns'][$colName];
        if ($colType && isset($this->config['types'][$colType])) {
            return $this->config['types'][$colType];
        }
        return $colType;
    }

    /**
     * @return string
     */
    public function getInsertStatements()
    {
        $result = [];
        while (($line = $this->reader->getLine(Reader::SKIP_EMPTY_LINES)) !== null) {
            $colValues = [];
            foreach ($line as $col) {
                $colValues[] = self::quoteValue($col);
            }
            // right pad line (if too short)
            for ($c = count($colValues); $c < count($this->reader->getHeader()); $c++) {
                $colValues[] = self::quoteValue(null);
            }
            $row = '(';
            $row .= implode(',', $colValues);
            $row .= ')';
            $result[] = $row;
        }
        // TODO: as `name` is MySQL specific, so  we need a generic quoteIdentifier() method
        // See e.g. https://www.sqlite.org/lang_keywords.html
        // or https://dev.mysql.com/doc/refman/5.7/en/identifiers.html
        /* for 'INSERT' not inside sprintf() see comment in getCreateTable() */
        return 'INSERT'.sprintf(' INTO `%s` VALUES ', $this->config['table']) . implode(',', $result) . ';';
    }

    /**
     * Quote a value (if needed) so it can be used in a SQL statement
     *
     * @param string $value string representation of the value (typically read from file)
     * @return string
     */
    public static function quoteValue($value)
    {
        if ('' === $value || null === $value) { return 'NULL'; }
        if (is_numeric($value)) { return $value; }
        // TODO (?) be more generic and don't use hardcoded '' double quotes (which might be MySQL specific)
        return sprintf("'%s'", str_replace("'", "''", $value));
    }

}