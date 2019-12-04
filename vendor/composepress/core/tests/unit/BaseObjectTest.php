<?php

use Test_Plugin\BaseObjectNamespaced;
class BaseObjectTest extends \Codeception\TestCase\WPTestCase
{
    public function test_getter()
    {
        $this->assertEquals('test', (new BaseObjectMock())->test);
    }
    public function test_setter()
    {
        $obj = new BaseObjectMock();
        $obj->test = 'new test';
        $this->assertEquals('new test', $obj->test);
    }
    public function test_global_getter()
    {
        $GLOBALS['global_test'] = 'test';
        $this->assertEquals('test', (new BaseObjectMock())->global_test);
    }
    public function test_global_setter()
    {
        $obj = new BaseObjectMock();
        $GLOBALS['global_test'] = 'test';
        $obj->global_test = 'new test';
        $this->assertEquals('new test', (new BaseObjectMock())->global_test);
    }
    public function test_is_getter()
    {
        $obj = new BaseObjectMock();
        $this->assertEquals('test', $obj->is_test);
    }
    public function test_setter_invalid()
    {
        $obj = new BaseObjectMock();
        $this->expectException('\\ComposePress\\Core\\Exception\\InexistentProperty');
        $obj->fail = 'fail';
    }
    public function test_is_setter_readonly()
    {
        $obj = new BaseObjectMock();
        $this->expectException('\\ComposePress\\Core\\Exception\\ReadOnly');
        $obj->is_test = 'new test';
    }
    public function test_get_setter_readonly()
    {
        $obj = new BaseObjectMock();
        $this->expectException('\\ComposePress\\Core\\Exception\\ReadOnly');
        $obj->get_test = 'new test';
    }
    public function test_isset_get_property()
    {
        $obj = new BaseObjectMock();
        $this->assertTrue(isset($obj->test));
    }
    public function test_isset_is_property()
    {
        $obj = new BaseObjectMock();
        $this->assertTrue(isset($obj->is_test));
    }
    public function test_isset_global()
    {
        $obj = new BaseObjectMock();
        $this->assertTrue(isset($obj->wpdb));
    }
    public function test_isset_global_invalid()
    {
        $obj = new BaseObjectMock();
        $this->assertFalse(isset($obj->fail));
    }
    public function test_get_full_class_name()
    {
        $obj = new BaseObjectNamespaced();
        $this->assertEquals( '\\Test_Plugin\\BaseObjectNamespaced', $obj->get_full_class_name());
    }
    public function test_get_class_name()
    {
        $obj = new BaseObjectMock();
        $this->assertEquals('BaseObjectMock', $obj->get_class_name());
    }
    public function test_get_file_name()
    {
        $obj = new BaseObjectMock();
        $this->assertTrue(is_string($obj->get_file_name()));
        $this->assertFileExists($obj->get_file_name());
    }
}
