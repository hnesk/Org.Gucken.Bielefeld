<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource,
	Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;
use Type\Feed;
use Org\Gucken\Events\Annotations as Events,
	TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @FLOW3\Scope("prototype")
 */
class Kunsthalle extends AbstractEventSource implements EventSourceInterface {

	/**
	 * @Events\Configurable
	 * @var \Org\Gucken\Events\Domain\Model\Location
	 */
	protected $location;

	/**
	 * @param Location $location
	 */
	public function setLocation($location) {
		$this->location = $location;
	}

	/**
	 *
	 * @return Location
	 */
	public function getLocation() {
		return $this->location;
	}

	/**
	 * @return \Type\Record\Collection
	 */
	public function getEvents() {
		return $this->getUrl()->loadBadHtml()->getContent()->css('#content div.event')->asXml()->map(array($this, 'getEvent'));
	}

	/**
	 *
	 * @param \Type\Xml $xml
	 * @return \Type\Record 
	 */
	public function getEvent(\Type\Xml $xml) {
		return new \Type\Record(array(
			'title'         => $xml->css('div.col_right>strong')->asString()->normalizeSpace(),
			'date'          => $xml->css('div.col_left')->asString()->normalizeSpace()->asDate('%d\|%m\D+%H:%M'),
			'enddate'       => $xml->css('div.col_left')->asString()->normalizeSpace()->asDate('%d\|%m[^-]+-\s*%H:%M'),
			'description'   => $xml->css('div.col_right p')->asXml()->join('div')->formattedText(),
			'type'          => $this->getTypeRepository()->findOneByKeywordString('Kunst'),
			'location'      => $this->getLocation(),
			'url'           => $xml->getBaseUri(),
		));
	}
}

?>
