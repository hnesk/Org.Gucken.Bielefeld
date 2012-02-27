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
class Bunker extends AbstractEventSource implements EventSourceInterface {

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
		return $this->getUrl()->load('badHtml')->getContent()->css('#uebersicht dl dt')->asXml()->map(array($this, 'getEvent'));
	}

	/**
	 *
	 * @param Xml $xml
	 * @return \Type\Record 
	 */
	public function getEvent(Xml $xml) {
		$url = $xml->xpath('./following-sibling::dd[1]//a/@href')->asUrl()->first();
		$description = $xml->xpath('./following-sibling::dd[1]/text()')->asString()->join()->normalizeSpace();
		if ($url && $url->is()) {
			$description = $description->append(PHP_EOL)->append(
				$url->load()->getContent()->css('#content')->asXml()->css('h3,p')
					->asString()->replace('Mehr hier in Kürze!', '')->normalizeParagraphs()
			);
		}

		return new \Type\Record(array(
			'title' => $xml->xpath('./following-sibling::dd[1]/strong//text()')->asString()->join()->normalizeSpace(),
			'short' => $xml->xpath('./following-sibling::dd[1]/text()')->asString()->join()->normalizeSpace(),
			'date' => $xml->text()->asDate('%a\s*%d.\s+%b%H:%M', null, 0, 0, time() - 14 * DAY),
			'location' => $this->getLocation(),
			'type' => $this->getType(),
			'description' => $description,
			'proof' => $xml,
			'url' => $url ? $url : $xml->getBaseUri()
		));
	}

}

?>
