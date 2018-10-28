<?php

use Fiedsch\Data\Utility\SqlCodeGenerator;
use Symfony\Component\Yaml\Yaml;
use PHPUnit\Framework\TestCase;

class SqlCodeGeneratorTest extends TestCase
{

    /**
     * @var string
     */
    protected $inputfile = 'tests/assets/data.txt';

    /**
     * @var string
     */
    protected $configfile = 'tests/assets/sqlgenerator_config.yml';

    /**
     * @var SqlCodeGenerator;
     */
    protected $generator;

    protected function setUp()
    {
        $config = Yaml::parse(file_get_contents($this->configfile));
        $this->generator = new SqlCodeGenerator($this->inputfile, $config);
    }

    protected function tearDown()
    {
        $this->generator->__destruct();
    }

    public function testCanBeInstatiated()
    {
        $this->assertInstanceOf(SqlCodeGenerator::class, $this->generator);
    }

    public function testInvalidConfigThrowsException()
    {
        $this->expectException('RuntimeException');
        new SqlCodeGenerator($this->inputfile, []);
    }

    public function testGetDropTable()
    {
        $expected = 'DROP TABLE IF EXISTS `testtable`;';
        $this->assertEquals($expected, $this->generator->getDropTable());
    }

    public function testCreateTableStatement()
    {
        $expectedSql = "CREATE TABLE `testtable` (`id` INTEGER,`name` VARCHAR(256),`age` DOUBLE);";
        $this->assertEquals($expectedSql, $this->generator->getCreateTable());
    }

    public function testInsertStatements()
    {
        $expectedSql = "INSERT INTO `testtable` VALUES (2,'Andreas',52),(3,'Fiedsch',NULL),(5,'John Doe',25);";
        $this->assertEquals($expectedSql, $this->generator->getInsertStatements());
    }

    public function  testQuoteValue()
    {
        $this->assertEquals(SqlCodeGenerator::quoteValue(null), "NULL");
        $this->assertEquals(SqlCodeGenerator::quoteValue(''), "NULL");
        $this->assertEquals(SqlCodeGenerator::quoteValue(1), "1");
        $this->assertEquals(SqlCodeGenerator::quoteValue(1.5), "1.5");
        $this->assertEquals(SqlCodeGenerator::quoteValue('x'), "'x'");
        $this->assertEquals(SqlCodeGenerator::quoteValue('O\'Shea'), "'O''Shea'");
    }


    public function  testQuoteNullIdentifier()
    {
        $this->expectException('TypeError');
        SqlCodeGenerator::quoteIdentifier(null);
    }

    public function  testQuoteEmptyIdentifier()
    {
        $this->expectException('RuntimeException');
        SqlCodeGenerator::quoteIdentifier('');
    }

    public function  testQuoteIdentifier()
    {
        $this->assertEquals(SqlCodeGenerator::quoteIdentifier('foo'), '`foo`');
    }
}
