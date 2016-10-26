<?php


class UtilsTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testIsEmail()
    {
        $test1 = "619101085@qq.com";
        $test2 = "619101085@.com";
        $test3 = "619101085@qq.cloud";
        $test4 = "@qq.com";
        $test5 = "619101085qq.com";

        $this->assertTrue(\NEUQOJ\Common\Utils::IsEmail($test1));
        $this->assertFalse(\NEUQOJ\Common\Utils::IsEmail($test2));
        $this->assertTrue(\NEUQOJ\Common\Utils::IsEmail($test3));
        $this->assertFalse(\NEUQOJ\Common\Utils::IsEmail($test4));
        $this->assertFalse(\NEUQOJ\Common\Utils::IsEmail($test5));
    }

    public function testCreateTimeStamp(){
        $time1 = \NEUQOJ\Common\Utils::createTimeStamp();
        sleep(1);
        $time2 = \NEUQOJ\Common\Utils::createTimeStamp();
        $this->assertTrue($time1 < $time2);
    }
}
