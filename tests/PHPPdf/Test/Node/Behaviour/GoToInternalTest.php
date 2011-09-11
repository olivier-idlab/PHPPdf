<?php

namespace PHPPdf\Test\Node\Behaviour;

use PHPPdf\Util\Point,
    PHPPdf\ObjectMother\NodeObjectMother,
    PHPPdf\Node\Container,
    PHPPdf\Node\Behaviour\GoToInternal;

class GoToInternalTest extends \PHPPdf\PHPUnit\Framework\TestCase
{
    private $objectMother;
    
    public function init()
    {
        $this->objectMother = new NodeObjectMother($this);
    }
    
    /**
     * @test
     */
    public function attachGoToActionToGraphicsContext()
    {
        $x = 0;
        $y = 500;
        $width = 100;
        $height = 100;
        
        $firstPoint = Point::getInstance(400, 300);
        
        $destination = $this->getMockBuilder('PHPPdf\Node\Container')
                            ->setMethods(array('getFirstPoint', 'getGraphicsContext', 'getNode'))
                            ->getMock();
                            
        $destination->expects($this->atLeastOnce())
                    ->method('getFirstPoint')
                    ->will($this->returnValue($firstPoint));
                    
        $destination->expects($this->atLeastOnce())
                    ->method('getNode')
                    ->will($this->returnValue($destination));
                    
        $gc = $this->getMockBuilder('PHPPdf\Engine\GraphicsContext')
                   ->getMock();
         
        $gc->expects($this->once())
           ->method('goToAction')
           ->with($gc, $x, $y, $x+$width, $y-$height, $firstPoint->getY());
           
        $destination->expects($this->atLeastOnce())
                    ->method('getGraphicsContext')
                    ->will($this->returnValue($gc));
                            
        $nodeStub = $this->getNodeStub($x, $y, $width, $height);
        
        $behaviour =  new GoToInternal($destination);
        
        $behaviour->attach($gc, $nodeStub);
    }
    
    private function getNodeStub($x, $y, $width, $height)
    {
        $boundary = $this->objectMother->getBoundaryStub($x, $y, $width, $height);
        
        $node = new Container();
        $this->invokeMethod($node, 'setBoundary', array($boundary));
        
        return $node;
    }
    
    /**
     * @test
     * @expectedException PHPPdf\Exception\Exception
     */
    public function throwExceptionIfDestinationIsEmpty()
    {
        $destination = $this->getMockBuilder('PHPPdf\Node\NodeAware')
                            ->getMock();
        $destination->expects($this->once())
                    ->method('getNode')
                    ->will($this->returnValue(null));

        $gc = $this->getMockBuilder('PHPPdf\Engine\GraphicsContext')
                   ->getMock();      
                    
        $behaviour =  new GoToInternal($destination);
        
        $behaviour->attach($gc, new Container());
    }
}