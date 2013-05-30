<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource,
	Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;
use Type\Url,
	Type\Xml,
	Type\Date,
	Type\Record;
use Org\Gucken\Events\Annotations as Events,
	TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("prototype")
 */
class Ostbahnhof extends AbstractEventSource implements EventSourceInterface {

	/**
	 * @Events\Configurable
	 * @var \Org\Gucken\Events\Domain\Model\Location
	 */
	protected $location;

	/**
	 * @Events\Configurable
	 * @var \Org\Gucken\Events\Domain\Model\Type
	 */
	protected $type;
	
	/**
	 * @param \Org\Gucken\Events\Domain\Model\Location $location
	 */
	public function setLocation($location) {
		$this->location = $location;
	}

	/**
	 *
	 * @param \Org\Gucken\Events\Domain\Model\Type $type 
	 */
	public function setType($type) {
		$this->type = $type;
	}

		

	/**
	 * @return \Type\Record\Collection
	 */
	public function getEvents() {
		return $this->getUrl()->load('badhtml')->getContent()
			->css('div#left_col_con div.ev_tease a')->asUrl()->load('badhtml')->getContent()
			->css('div#right_col_con')->asXml()
			->map(array($this, 'getEvent'));
	}

	/**
	 * @return \Type\Record\Collection
	 */
	public function getEvent(\Type\Xml $xml) {	
		$title = $xml->css('.event_detail_headline')->asString()->normalizeSpace();
				
		return $title->contains('Geschlossen') ? null : new \Type\Record(array(
			'date' => $xml->css('.event_detail_date')->asString()->normalizeSpace()->asDate('%d.%m.%Y - %H[:.]%M'),
			'title' => $xml->css('.event_detail_headline')->asString()->normalizeSpace(),
			'image' => $xml->css('.event_detail_img img')->asUrl()->first(),
			'description' => $xml->css('.event_detail_text')->asXml()->join()->markdown(),
			'type' => $this->type,
			'location' => $this->location,
			'url' => $xml->getBaseUri()
		));
		
	}

}

?>
