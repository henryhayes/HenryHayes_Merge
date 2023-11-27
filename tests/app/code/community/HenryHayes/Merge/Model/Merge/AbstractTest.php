<?php
use PHPUnit\Framework\TestCase;

final class HenryHayes_Merge_Model_Merge_AbstractTest extends TestCase
{
    public function testConcreteMethod()
    {
        $stub = $this->getMockForAbstractClass(
            'HenryHayes_Merge_Model_Merge_Abstract',
            [] /* arguments */,
            '' /* mockClassName */,
            false /* callOriginalConstructor */
        );

        $this->assertInstanceOf('HenryHayes_Merge_Model_Merge_Abstract', $stub);
    }
}

