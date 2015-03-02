<?php
namespace Xoops\Form;

require_once(dirname(__FILE__).'/../../../init_mini.php');

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-08-18 at 21:59:23.
 */
 
/**
 * PHPUnit special settings :
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */

class ButtonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Button
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new Button('button_caption', 'button_name', 'button_value');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Xoops\Form\Button::getType
     */
    public function testGetType()
    {
        $value = $this->object->getType();
        $this->assertSame('button',$value);
    }

    /**
     * @covers Xoops\Form\Button::render
     */
    public function testRender()
    {
        $value = $this->object->render();
        $this->assertTrue(false !== strpos($value, '<input'));
        $this->assertTrue(false !== strpos($value, 'type="button"'));
        $this->assertTrue(false !== strpos($value, 'name="button_name"'));
        $this->assertTrue(false !== strpos($value, 'id="button_name"'));
        $this->assertTrue(false !== strpos($value, 'title="button_caption"'));
        $this->assertTrue(false !== strpos($value, 'value="button_value"'));
    }
}