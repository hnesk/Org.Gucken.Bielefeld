<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource,
	Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;
use Type\Xml;
use Org\Gucken\Events\Annotations as Events,
	TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @FLOW3\Scope("prototype")
 */
class Stadtbibliothek extends AbstractEventSource implements EventSourceInterface {


	/**
	 * @Events\Configurable
	 * @var \Org\Gucken\Events\Domain\Model\Type
	 */
	protected $type;


	/**
	 *
	 * @param \Org\Gucken\Events\Domain\Model\Type $type
	 */
	public function setType(\Org\Gucken\Events\Domain\Model\Type $type) {
		$this->type = $type;
	}

	/**
	 * @return \Org\Gucken\Events\Domain\Model\Type
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return \Type\Record\Collection
	 */
	public function getEvents() {
		return $this->getUrl()->load('badHtml')->getContent()
			->css('select#eventsearch option')->xpath('/@value')->asUrl()->load('badHtml')->getContent()
			->css('div.moduleEventTable tbody tr')->asXml()->map(array($this, 'getEvent'));
	}

	/**
	 *
	 * @param Xml $xml
	 * @return \Type\Record 
	 */
	public function getEvent(Xml $tr) {		
		
		return new \Type\Record(array(
			'title' => $xml->css('tr.eventName div.eventName')->asString()->normalizeSpace(),
			'date' => $date = $xml->css('tr.when div.date, tr.when div.performance span.time')->asString()->normalizeSpace()->asDate('%d. %B %Y( %H:%M)?'),
			'location' => $this->getLocationRepository()->findOneByKeywordString($location),
			'type' => $this->getType(),
			'description' => $xml->css('tr.other div.notes')->asString()->normalizeParagraphs(),
			'proof' => $xml,
			'url' => $xml->getBaseUri()
		));
	}

}

?>
