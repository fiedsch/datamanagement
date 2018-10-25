<?php

use Fiedsch\Data\Utility\SqlGenerator;
use Symfony\Component\Yaml\Yaml;
use PHPUnit\Framework\TestCase;

class SqlGeneratorTest extends TestCase
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
     * @var SqlGenerator;
     */
    protected $generator;

    protected function setUp()
    {
        $config = Yaml::parse(file_get_contents($this->configfile));
        $this->generator = new SqlGenerator($this->inputfile, $config);
    }

    protected function tearDown()
    {
        $this->generator->__destruct();
    }

    public function testCanBeInstatiated()
    {
        $this->assertInstanceOf(SqlGenerator::class, $this->generator);
    }

    public function testInvalidConfigThrowsException()
    {
        $this->expectException('RuntimeException');
        new SqlGenerator($this->inputfile, []);
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
        $this->assertEquals(SqlGenerator::quoteValue(null), "NULL");
        $this->assertEquals(SqlGenerator::quoteValue(''), "NULL");
        $this->assertEquals(SqlGenerator::quoteValue(1), "1");
        $this->assertEquals(SqlGenerator::quoteValue(1.5), "1.5");
        $this->assertEquals(SqlGenerator::quoteValue('x'), "'x'");
        $this->assertEquals(SqlGenerator::quoteValue('O\'Shea'), "'O''Shea'");
    }

}
