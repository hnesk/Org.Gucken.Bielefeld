<?php
namespace Org\Gucken\Bielefeld\Tests\Unit\Domain\Model;

require_once FLOW3_PATH_PACKAGES.'/Application/Org.Gucken.Events/Tests/EventSourceUnitTestCase.php';

class OffkinoTest extends \Org\Gucken\Events\Tests\EventSourceUnitTestCase {
		
	public function setUp() {
		$this->baseUrl = 'file://'.realpath(__DIR__ . '/../../Fixtures');
		$this->source = new \Org\Gucken\Bielefeld\Domain\Model\Offkino();
	}
	
	public function tearDown() {
		$this->baseUrl = '';
		$this->source = null;
	}		

	
	/**
	 * @test 
	 * @dataProvider getData
	 */
	public function eventsHaveCorrectData($file, $nr, $data) {
		
		$this->markTestIncomplete('cant follow local urls yet');
		#return $this->assertEventDataIsCorrect($file, $nr, $data);
	}	

	
	
	public static function getData() {
		return array(
			array('Offkino/Programm20120114.html',0, array(
				'title'		=> 'Lebenszeichen',				
				'image'		=> 'http://images/stories/lebenszeichen.jpg',
				'date'		=> new \DateTime('2011-12-16T20:00'),
				'short' => 'Einlass: 19 Uhr Beginn:20 Uhr VVK: 16,-'
			)),
		);
	}
	
	
}
?>
