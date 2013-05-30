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
class GerryWeber extends AbstractEventSource implements EventSourceInterface {

	/**
	 * @Events\Configurable
	 * @var \Org\Gucken\Events\Domain\Model\Location
	 */
	protected $location;


	/**
	 * @param \Org\Gucken\Events\Domain\Model\Location $location
	 */
	public function setLocation($location) {
		$this->location = $location;
	}
	

	/**
	 * @return \Type\Record\Collection
	 */
	public function getEvents() {
		return $this->getUrl()->load('badhtml')->getContent()
			->css('div.content a.pfeil-orange')->asUrl()->load('badhtml')->getContent('iconv.UTF-8=ISO-8859-1')
			->css('div.content')->asXml()->map(array($this, 'getEvent'));
	}

	/**
	 * @return \Type\Record\Collection
	 */
	public function getEvent(\Type\Xml $xml) {			
		$title = $xml->css('.headline h1')->asString()->normalizeSpace();
		$teaser = $xml->css('.headline div')->asString()->first()->normalizeSpace();
		$date = $xml->css('.headline p.subline')->asString()->normalizeSpace()->asDate('%d. %B\D+%Y um %H.%M Uhr');
		
		return !(is($date) && $date instanceof \Type\Date) ? null : new \Type\Record(array(
			'title' => $title,
			'date' => $date,
			'short' => $teaser,
			'description' => $xml->css('.details .text')->asXml()->join()->markdown(),
			'location' => $this->location,
			'type' => $this->typeRepository->findOneByKeywordString($teaser.' '.$title),
			'url' => $xml->getBaseUri()
			
		));
	}

}

?>
