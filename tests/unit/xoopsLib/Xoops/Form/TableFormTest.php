<?php
namespace Xoops\Form;

require_once(dirname(__FILE__).'/../../../init_new.php');

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-08-18 at 21:59:26.
 */
 
/**
 * PHPUnit special settings :
 * @backupGlobals disabled
 * @backupStaticAttributes disabled
 */

class TableFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TableForm
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new TableForm('Caption', 'name', 'action');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @covers Xoops\Form\TableForm::insertBreak
     */
    public function testInsertBreak()
    {
        $this->object->insertBreak();
        $value = $this->object->render();
        $this->assertTrue(false !== strpos($value, '<tr valign="top" align="left"><td></td></tr>'));
    }

    /**
     * @covers Xoops\Form\TableForm::render
     */
    public function testRender()
    {
        $value = $this->object->render();
        $this->assertTrue(is_string($value));
        $this->assertTrue(false !== strpos($value, '<form'));
        $this->assertTrue(false !== strpos($value, 'name="name"'));
        $this->assertTrue(false !== strpos($value, 'id="name"'));
        $this->assertTrue(false !== strpos($value, 'action="action"'));
        $this->assertTrue(false !== strpos($value, 'method="post"'));
        $this->assertTrue(false !== strpos($value, '</form>'));
    }
}
