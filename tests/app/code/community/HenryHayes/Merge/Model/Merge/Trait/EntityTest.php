<?php
use PHPUnit\Framework\TestCase;

final class HenryHayes_Merge_Model_Merge_EntityTest extends TestCase
{
    protected function setUp(): void
    {
        $this->stack = [];
    }
    
    public function testConcreteMethod(): void
    {
        /** @var HenryHayes_Merge_Model_Merge_Trait_Entity $sut */
        $sut = $this->getMockForTrait('HenryHayes_Merge_Model_Merge_Trait_Entity');
        $mock = $this->getMockForAbstractClass('Mage_Core_Model_Abstract', [], '', false);

        $sut->expects($this->once())
            ->method('setEntity')
            ->will($this->returnSelf());

        $sut->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($mock));
        
        $sut->setEntity($mock);
        $sut->getEntity();

        $this->assertTrue($mock->concreteMethod());
    }
}

