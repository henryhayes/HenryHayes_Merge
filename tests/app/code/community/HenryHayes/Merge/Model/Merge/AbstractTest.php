<?php
use PHPUnit\Framework\TestCase;

final class HenryHayes_Merge_Model_Merge_AbstractTest extends TestCase
{
    public function testConcreteMethod(): void
    {
        $stub = $this->getMockForAbstractClass('HenryHayes_Merge_Model_Merge_Abstract');

        $this->assertInstanceOf('HenryHayes_Merge_Model_Merge_Abstract', $stub);
    }
}

