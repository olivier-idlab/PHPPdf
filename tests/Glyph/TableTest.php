<?php

use PHPPdf\Glyph\Table;
use PHPPdf\Util\Boundary;
use PHPPdf\Glyph as Glyphs;

class TableTest extends PHPUnit_Framework_TestCase
{
    private $table = null;

    public function setUp()
    {
        $this->table = new Table();
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function addingInvalidChild()
    {
        $glyph = new Glyphs\Container();
        $this->table->add($glyph);
    }

    /**
     * @test
     */
    public function addingValidChild()
    {
        $glyph = new Glyphs\Table\Row();
        $this->table->add($glyph);

        $this->assertTrue(count($this->table->getChildren()) > 0);
    }

    /**
     * @test
     */
    public function rowsAndCellsAttributes()
    {
        $height = 40;
        $this->table->setRowHeight($height);
        
        $this->assertEquals($height, $this->table->getRowHeight());
    }
    
    /**
     * @test
     */
    public function split()
    {
        $numberOfRows = 10;
        $heightOfRow = 50;
        $tableHeight = 500;

        $boundary = $this->table->getBoundary();
        $boundary->setNext(0, $tableHeight)
                 ->setNext(200, $tableHeight)
                 ->setNext(200, 0)
                 ->setNext(0, 0)
                 ->close();

        $this->table->setHeight(500)->setWidth(200);

        $start = 500;
        $pointOfSplit = 220;
        $reversePointOfSplit = $start - $pointOfSplit;
        $rowSplitOccurs = false;
        for($i=0; $i<$numberOfRows; $i++)
        {
            $end = $start-$heightOfRow;
            $split = $reversePointOfSplit < $start && $reversePointOfSplit > $end;
            if($split)
            {
                $rowSplitOccurs = true;
            }

            $mock = $this->createRowMock(array(0, $start), array(200, $end), $split, $rowSplitOccurs);
            $this->table->add($mock);
            $start = $end;
        }

        $result = $this->table->split(220);

        $this->assertNotNull($result);
        $this->assertEquals($numberOfRows, count($result->getChildren()) + count($this->table->getChildren()));
        $this->assertEquals($tableHeight, $result->getHeight() + $this->table->getHeight());
    }

    private function createRowMock($start, $end, $split = false, $translate = false)
    {
        $methods = array('getHeight', 'getBoundary');
        if($split)
        {
            $methods[] = 'split';
        }
        if($translate)
        {
            $methods[] = 'translate';
        }

        $mock = $this->getMock('PHPPdf\Glyph\Table\Row', $methods);

        if($split)
        {
            $mock->expects($this->once())
                 ->method('split')
                 ->will($this->returnValue(null));
        }

        $boundary = new Boundary();
        $boundary->setNext($start[0], $start[1])
                 ->setNext($end[0], $start[1])
                 ->setNext($end[0], $end[1])
                 ->setNext($start[0], $end[1])
                 ->close();

        $height = $start[1] - $end[1];

        $mock->expects($this->atLeastOnce())
             ->method('getBoundary')
             ->will($this->returnValue($boundary));
        $mock->expects($this->any())
             ->method('getHeight')
             ->will($this->returnValue($height));

        if($translate)
        {
            $mock->expects($this->atLeastOnce())
                 ->method('translate');
        }

        return $mock;
    }
}