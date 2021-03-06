<?php
namespace Xmf\Key;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-02-06 at 23:03:00.
 */
 
/**
* PHPUnit special settings :
* @backupGlobals disabled
* @backupStaticAttributes disabled
*/
class ArrayStorageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ArrayStorage
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new ArrayStorage;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Xmf\Key\ArrayStorage::save
     */
    public function testSave()
    {
        $name = 'name';
        $data = 'data';
        $this->object->save($name, $data);
        $this->assertEquals($data, $this->object[$name]);
    }

    /**
     * @covers Xmf\Key\ArrayStorage::fetch
     */
    public function testFetch()
    {
        $name = 'name';
        $data = 'data';
        $this->assertFalse($this->object->fetch($name));
        $this->object->save($name, $data);
        $this->assertEquals($this->object->fetch($name), $data);
    }

    /**
     * @covers Xmf\Key\ArrayStorage::exists
     */
    public function testExists()
    {
        $name = 'name';
        $data = 'data';
        $this->assertFalse($this->object->exists($name));
        $this->object->save($name, $data);
        $this->assertTrue($this->object->exists($name));
    }

    /**
     * @covers Xmf\Key\ArrayStorage::delete
     */
    public function testDelete()
    {
        $name = 'name';
        $data = 'data';
        $this->object->save($name, $data);
        $this->assertTrue($this->object->exists($name));
        $actual = $this->object->delete($name);
        $this->assertTrue($actual);
        $actual = $this->object->delete($name);
        $this->assertFalse($actual);
        $this->assertFalse($this->object->exists($name));
    }
}
