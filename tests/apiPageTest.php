<?php

class ApiPageTest extends PHPUnit_Framework_TestCase
{
    /**
     * testThrowsFormatError
     *
     * @test
     * @expectedException APIPageTestException
     * @convers APIPage::setOutput
     */
    public function testThrowsFormatError()
    {
        $page = $this->getPageMockObj();

        //setting default format to an unknowen value should throw an exception
        $apipage = new ApiPage($page, array(
            'default-format' => 'python',
            'param-selector' => 'url-format'
        ));

        $apipage->setOutput(function () {
            throw new APIPageTestException('test');
        });
    }

    /**
     * testSetTrigger
     *
     * @test
     * @convers APIPage::setOutput
     */
    public function testSetTrigger()
    {
        $page = $this->getPageMockObj();

        $apipage = new ApiPage($page, array(
            'default-format' => 'json',
            'param-selector' => 'url-format'
        ));

        $this->assertFalse($apipage->trigger);

        $apipage->setOutput(function () {
            throw new APIPageTestException('test');
        });

        $this->assertTrue($apipage->trigger);
    }

    /**
     * getPageMockObj
     *
     * @param array $conf
     * @access protected
     * @return void
     */
    protected function getPageMockObj(array $conf = array())
    {
        $page = $this->getMock('FrontendPage', array('addHeaderToPage', 'Params'));
        $page
            ->expects($this->any())
            ->method('addHeaderToPage')
            ->will($this->returnValue(null));
        $page
            ->expects($this->any())
            ->method('Params')
            ->will($this->returnValue(!empty($conf) ? $conf : array(
                'page-types' => array('API')
            )));

        return $page;
    }
}

class APIPageTestException extends \Exception {}
