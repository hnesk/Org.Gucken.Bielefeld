<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource,
	Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;
use Type\Url,
	Type\Xml;
use Org\Gucken\Events\Annotations as Events,
	TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("prototype")
 */
class Stereo extends AbstractEventSource implements EventSourceInterface {

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
	 * @return \Org\Gucken\Events\Domain\Model\Location
	 */
	public function getLocation() {
		return $this->location;
	}

	/**
	 * @param \Org\Gucken\Events\Domain\Model\Type $type
	 */
	public function setType($type) {
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
				->css('#maininhalt div.inhaltwidth3>table tr')->asXml()
				->map(array($this, 'getEvent'));
	}

	/**
	 * 
	 * @param \Type\Xml $xml
	 * @return \Type\Record 
	 */
	public function getEvent(Xml $xml) {
		
		$moreUrl = $xml->xpath('td[3]')->css('a.linkliste')->asUrl()->first();
		/* @var $moreUrl Type\Url */
		$moreXml = $moreUrl->trim(\Type\Url::NOFRAGMENT)->loadBadHtml()->getContent();
		/* @var $moreXml Type\Xml */
		
		$description = $moreXml->fragment($moreUrl->getFragment())->xpath('./following-sibling::div[1]//div[contains(@class,"textbox1")]')->asXml()->slice(2)->join('div')->formattedText();
		$description = $description->remove('[]')->normalizeParagraphs();
		
		/** @todo evtl über die veranstaltungen loopen ? */
		return new \Type\Record(array(
				'title' => $xml->xpath('td[3]')->css('.linkliste')->asString()->first(),
				// @todo ist noch halbgar: uhrzeit ziehen
				'date' => $xml->xpath('td[2]')->asString()->first()->normalizeSpace()->asDate('%d.%m.%y'),
				'description' => $description,
				'url' => $moreUrl,
				'location' => $this->getLocation(),
				'type'  => $this->getType(),
				#'proof' => $xml
			));
	}

}

?>
