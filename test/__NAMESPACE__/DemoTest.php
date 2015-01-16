<?php

use AspectMock\Test as test;

class DemoTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        // This is a good place to set up any test doubles that you
        // will be using in all tests for DRY code :)
    }

    public function tearDown()
    {
        // Always clean test doubles registry between tests to prevent
        // potential conflicts and unnecessary confusion.
        test::clean();
    }

    public function testModelCall()
    {
        // This test demonstrates how to create a test double for Mage
        // Normally you would be testing a separate classes method so
        // some of the logic below would be in a separate file, obvs.

        // Using PhpUnits mock builder we can create a stub object for
        // a Magento core file. Which allows us to control what the
        // method calls return and also assert they are invoked, etc.
        $mockModel = $this->getMockBuilder('Mage_Catalog_Model_Product')
            ->setMethods(array('getSku'))
            ->getMock();

        $mockModel
            ->expects($this->once())
            ->method('getSku')
            ->will($this->returnValue('123456789'));

        // Because getting a model in Magento consists of a static call
        // it makes testing difficult. To counter this we are using a
        // library called AspectMock which uses Go! AOP to allow us to
        // create test doubles for static method calls. WIN!
        $modelStub = test::double('Mage', ['getModel' => $mockModel]);

        // This is the logic we would be testing
        $model  = Mage::getModel('catalog/product');
        $sku    = $model->getSku();

        // Because our $modelStub returned our $mockModel when Mage::getModel()
        // is called $model is the same as our $mockModel. By calling getSku()
        // we are using the stubbed model method which returns our defined result.
        $this->assertSame('123456789', $sku);

        // We will also want to assert that the stubbed static method was
        // actually called.
        $modelStub->verifyInvokedOnce('getModel', 'catalog/product');
    }
}