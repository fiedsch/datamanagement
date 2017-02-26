<?php

use Fiedsch\Data\Utility\TokenCreator;
use PHPUnit\Framework\TestCase;

class TokenCreatorTest extends TestCase
{

    public function testDefaultLength()
    {
        $creator = new TokenCreator();
        $this->assertEquals(TokenCreator::DEFAULT_LENGTH, strlen($creator->getUniqueToken()));
    }

    public function testSpecifiedLength()
    {
        $creator = new TokenCreator(10);
        $this->assertEquals(10, strlen($creator->getUniqueToken()));
    }

    public function testDefaultCase()
    {
        $creator = new TokenCreator();
        $token = $creator->getUniqueToken();
        $this->assertEquals($token, strtoupper($token));
    }

    public function testSpecifiedCase()
    {
        $creator = new TokenCreator(10, TokenCreator:: LOWER);
        $token = $creator->getUniqueToken();
        $this->assertEquals($token, strtolower($token));

        $creator = new TokenCreator(10, TokenCreator:: UPPER);
        $token = $creator->getUniqueToken();
        $this->assertEquals($token, strtoupper($token));
    }
}
