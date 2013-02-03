<?php

class XmlToArrayTest extends PHPUnit_Framework_TestCase
{

    /**
     * testToArray
     *
     * @dataProvider providesBasicXML
     */
    public function testToArray($xml)
    {
        $parser = new XmlToArray($xml);
        $array = $parser->parse();
        $this->assertArrayHasKey('data', $array);
        $this->assertArrayHasKey('node', $array['data']);
    }

    /**
     * testNestedXMLToArray
     * @dataProvider providesNestedXML
     *
     */
    public function testNestedXMLToArray($xml)
    {
        $parser = new XmlToArray($xml);
        $array = $parser->parse();

        $this->assertArrayHasKey('firstnode', $array['data']);
        $this->assertArrayHasKey('secondnode', $array['data']['firstnode']);
        $this->assertArrayHasKey('thirdnode', $array['data']['firstnode']);
    }

    /**
     * @dataProvider providesNestedXML
     */
    public function testParseAttributes($xml)
    {
        $parser = new XmlToArray($xml);
        $array = $parser->parse();

        $this->assertTrue(isset($array['data']['alone-and-attributes']) && isset($array['data']['alone-and-attributes']['@attributes']));
    }

    /**
     * @dataProvider providesNestedXML
     */
    public function testParseArrays($xml)
    {
        $parser = new XmlToArray($xml);
        $array = $parser->parse();
        $this->assertTrue(isset($array['data']['arrayish']['item']));
        $this->assertContains('foo', $array['data']['arrayish']['item']);
        $this->assertContains('bar', $array['data']['arrayish']['item']);
        $this->assertContains('baz', $array['data']['arrayish']['item']);
        var_dump($array);
    }

    /**
     * @dataProvider providesComplexXML
     */
    public function testParseEmptyNodesWithAttributes($xml)
    {
        $parser = new XmlToArray($xml);
        $array = $parser->parse();

        $this->assertTrue(isset($array['response']['empty']));
        $this->assertTrue(isset($array['response']['empty']['@attributes']));
        $this->assertTrue(isset($array['response']['empty']['@attributes']['id']));
        $this->assertTrue(isset($array['response']['odd-content']));
        $this->assertTrue(is_null($array['response']['odd-content']['emptynode']));
        $this->assertTrue(isset($array['response']['odd-content']['entry']));
        $this->assertEquals(3, count($array['response']['odd-content']['entry']));
    }

    /**
     * @dataProvider providesXMLWithZeroValue
     */
    public function testParseXMLWithZeroValue($xml)
    {
        $parser = new XmlToArray($xml);
        $array = $parser->parse();
        $this->assertEquals('0', $array['response']['node']);
    }
    /**
     * @dataProvider providesXMLWithZeroValue
     */
    public function testParseXMLWithEmptyNodes($xml)
    {
        $parser = new XmlToArray($xml);
        $array = $parser->parse();
        $this->assertEquals('', $array['response']['empty']);
    }



    /**
     * providesBasicXML
     */
    public function providesBasicXML()
    {
        return array(array(file_get_contents(dirname(__FILE__) . '/basic.xml')));
    }

    /**
     * providesNestedXML
     */
    public function providesNestedXML()
    {
        return array(array(file_get_contents(dirname(__FILE__) . '/nested.xml')));
    }

    /**
     * providesComplexXML
     */
    public function providesComplexXML()
    {
        return array(array(file_get_contents(dirname(__FILE__) . '/complex.xml')));
    }

    /**
     * providesXMLWithZeroValue
     */
    public function providesXMLWithZeroValue()
    {
        return array(array(file_get_contents(dirname(__FILE__) . '/nullvalue.xml')));
    }

}
