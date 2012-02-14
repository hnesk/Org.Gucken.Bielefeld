<?php
namespace Org\Gucken\Bielefeld\Tests\Unit\Domain\Model;

require_once FLOW3_PATH_PACKAGES.'/Application/Org.Gucken.Events/Tests/EventSourceUnitTestCase.php';

class KunstvereinTest extends \Org\Gucken\Events\Tests\EventSourceUnitTestCase {
		
	public function setUp() {
		$this->baseUrl = 'file://'.realpath(__DIR__ . '/../../Fixtures');
		$this->source = new \Org\Gucken\Bielefeld\Domain\Model\Kunstverein();
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
		return $this->assertEventDataIsCorrect($file, $nr, $data);
	}	

	
	public static function getData() {
		return array(
			array('Kunstverein/rss.xml',0, array(
				'title'		=> 'Eröffnung der Ausstellung »Carl Strüwe im Kontext zeitgenössischer Fotografie«.',				
				'date'		=> new \DateTime('2012-02-05T11:30'),
				'description' => 'Beginn um 11:30 Uhr in der Kunsthalle.',
				'url' => 'http://www.bielefelder-kunstverein.de/ausstellungen/2012/carl-struewe-im-kontext-zeitgenoessischer-fotografie.html'
			)),
		);
	}
	
}
?>
