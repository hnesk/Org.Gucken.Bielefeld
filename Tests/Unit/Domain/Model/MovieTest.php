<?php
namespace Org\Gucken\Bielefeld\Tests\Unit\Domain\Model;

require_once FLOW_PATH_PACKAGES.'/Application/Org.Gucken.Events/Tests/EventSourceUnitTestCase.php';

class MovieTest extends \Org\Gucken\Events\Tests\EventSourceUnitTestCase {
		
	public function setUp() {
		$this->baseUrl = 'file://'.realpath(__DIR__ . '/../../Fixtures');
		$this->source = new \Org\Gucken\Bielefeld\Domain\Model\Movie();
	}
	
	public function tearDown() {
		$this->baseUrl = '';
		$this->source = null;
	}		

	/**
	 * @test 
	 */
	public function findsAllUrls() {
		$expected = array(
			'http:///konzerte-a-live-events/aktuell/event/39-ashleigh-flynn',
			'http:///konzerte-a-live-events/aktuell/event/40-scherbekontrabass',
			'http:///konzerte-a-live-events/aktuell/event/38-terry-hoax',
			'http:///konzerte-a-live-events/aktuell/event/42-nothing-tightless',
		);
		$this->source->setUrl($this->baseUrl.'/Movie/konzerte-aktuell.html');
		$urls = $this->source->getUrls()->getNativeValue();
		$this->assertEquals($expected, $urls);

	}
	
	/**
	 * @test 
	 * @dataProvider getData
	 */
	public function eventsHaveCorrectData($file, $nr, $data) {
		$this->assertEventDataIsCorrect($file, $nr, $data);
	}	

	
	public static function getData() {
		return array(
			array('Movie/konzerte-aktuell.html',0, array(
				'title'		=> 'Ashleigh Flynn',				
				'date'		=> new \DateTime('2012-02-08T20:00'),
				'description' => '###Ashleigh Flynns scheint den amerikanischen Traum nicht aufgeben zu wollen, nein vielmehr will sie ihn neu beleben.',
				'url' => 'http://www.movie-bielefeld.de/konzerte-a-live-events/aktuell/event/39-ashleigh-flynn',
				'image' => 'http://www.movie-bielefeld.de/images/stories/movie-bielefeld/events/flynn2',
				'cost_box_office' => 10.0,
			)),
		);
	}
	
}
?>
