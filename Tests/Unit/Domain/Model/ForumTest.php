<?php
namespace Org\Gucken\Bielefeld\Tests\Unit\Domain\Model;

require_once FLOW_PATH_PACKAGES.'/Application/Org.Gucken.Events/Tests/EventSourceUnitTestCase.php';

class ForumTest extends \Org\Gucken\Events\Tests\EventSourceUnitTestCase {
		
	public function setUp() {
		$this->baseUrl = 'file://'.realpath(__DIR__ . '/../../Fixtures');
		$this->source = new \Org\Gucken\Bielefeld\Domain\Model\Forum();
	}
	
	public function tearDown() {
		$this->baseUrl = '';
		$this->source = null;
	}		

	
	/**
	 * @test 
	 * @dataProvider getConcertData
	 */
	public function concertsHaveCorrectData($file, $nr, $data) {
		return $this->assertEventDataIsCorrect($file, $nr, $data);
	}	

	/**
	 * @test 
	 * @dataProvider getPartyData
	 */
	public function partysHaveCorrectData($file, $nr, $data) {
		return $this->assertEventDataIsCorrect($file, $nr, $data);
	}
	
	
	public static function getConcertData() {
		return array(
			array('Forum/Konzerte.html',0, array(
				'title'		=> 'PRINZ PI, HERR VON GRAU',				
				'image'		=> 'http://img/band/prinzpi.jpg',
				'description' => '###Prinz PI hat ein neues Album rausgehauen, dass sofort',
				'date'		=> new \DateTime('2012-01-12T20:00'),
				'cost_pre_selling' => 16.0,
				'cost_box_office' => null,
				'short' => 'Einlass: 19 Uhr Beginn:20 Uhr VVK: 16,-'
			)),
			array('Forum/Konzerte.html',1, array(
				'title'		=> 'BEVIS FROND (GB)',				
				'image'		=> 'http://img/band/BevisFrond_PressImage001.jpg',
				'description' => '###Nick Saloman gilt als einer der produktivsten Köpfe britischen Psychedelic-Rocks. Unter dem Namen THE BEVIS FROND nahm er bisher zwar 17 Alben',
				'date'		=> new \DateTime('2012-01-17T21:00'),
				'cost_pre_selling' => 10.0,
				'cost_box_office' => 13.0,
				'short'		=> 'Einlass: 20.30 Uhr Beginn:21 Uhr VVK: 10,- AK: 13,-'
			)),
			array('Forum/Konzerte.html',2, array(
				'title'		=> 'GWAR (USA), SISTER, COLLAPSE',				
				'image'		=> null,
				'description' => '###Fällt aus!',
				'date'		=> new \DateTime('2012-01-26T20:30'),
				'short'		=> 'Einlass: 20 Uhr Beginn:20.30 Uhr fällt aus!'
			)),
			
			array('Forum/Konzerte.html',3, array(
				'title'		=> 'SIGHTS & SOUNDS (CAN), CONSTANTS (USA)',				
				'image'		=> 'http://img/band/sightsandsounds.jpg',								
				'description' => '###Sights & Sounds besteht aus Mitgliedern namhafter Bands wie Comeback Kid, Sick City und Figure Four und bringt somit eine einzigartige und talentierte Gruppe von Musikern zusammen',
				'date'		=> new \DateTime('2012-01-29T21:00'),
				'cost_pre_selling' => 10.0,
				'cost_box_office' => 13.0,
				'short'		=> 'Einlass: 20.30 Uhr Beginn:21 Uhr VVK: 10,- AK: 13,-',
			)),
			
			
			
			
				
		);
	}
	
	
	public static function getPartyData() {
		return array(
			array('Forum/Parties.html',0, array(
				'title'		=> 'UNVERNUNFT-PARTY 2012',				
				'image'		=> 'http://img/index/Unvernunft-eflyer.jpg',
				'short'		=> 'live: Gebrüder TEICHMANN / THE VON DUESZ',
				'date'		=> new \DateTime('2012-01-07T23:00'),
			)),
			array('Forum/Parties.html',1, array(
				'title'		=> 'SWEET SOUL MUSIC CLUB',				
				'image'		=> 'http://img/index/ssmc112.jpg',
				'short'		=> '',
				'date'		=> new \DateTime('2012-01-13T23:00'),
			)),
			array('Forum/Parties.html',2, array(
				'title'		=> 'ELECTRONIC LOUNGE',				
				'image'		=> 'http://img/index/el112.jpg',
				'short'		=> '',
				'date'		=> new \DateTime('2012-01-14T23:00'),
			)),
			array('Forum/Parties.html',3, array(
				'title'		=> 'ANGY\'S DARK SCENE-PARTY',				
				'image'		=> 'http://img/index/dark-szene_tdu_front_787x60.jpg',
				'short'		=> '20.30',
				'date'		=> new \DateTime('2012-01-20T20:30'),
			)),
			array('Forum/Parties.html',4, array(
				'title'		=> 'R!',				
				'image'		=> 'http://img/index/r112.jpg',
				'short'		=> 'GUITAR BEATS POP',
				'date'		=> new \DateTime('2012-01-21T23:00'),
			)),
			array('Forum/Parties.html',5, array(
				'title'		=> 'MUSIK L(I)EBEN',				
				'image'		=> null,
				'short'		=> '19.30 Uhr',
				'date'		=> new \DateTime('2012-01-27T19:30'),
			)),
			array('Forum/Parties.html',6, array(
				'title'		=> 'HIMMEL UND ERDE',				
				'image'		=> 'http://img/index/hue112.jpg',
				'short'		=> '',
				'date'		=> new \DateTime('2012-01-28T23:00'),
			)),
			array('Forum/Parties.html',7, array(
				'title'		=> 'MUSIK L(I)EBEN',				
				'image'		=> null,
				'short'		=> '19.30 Uhr',
				'date'		=> new \DateTime('2012-01-31T19:30'),
			)),
			array('Forum/Parties.html',8, array(
				'title'		=> '80s HAIR METAL-PARTY',				
				'image'		=> null,
				'short'		=> 'ab ca 0 Uhr im Anschluss an das A PALE HORSE NAMED DEATH-Konzert',
				'date'		=> new \DateTime('2012-02-04T23:00'),
			)),						
		);
	}
}
?>
