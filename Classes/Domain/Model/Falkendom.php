<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource,
	Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;
use Type\Feed,
	Type\Xml;
use Org\Gucken\Events\Annotations as Events,
	TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @FLOW3\Scope("prototype")
 */
class Falkendom extends AbstractEventSource implements EventSourceInterface {

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
	 * @return Location 
	 */
	public function getLocation() {
		return $this->location;
	}

	/**
	 * @return \Type\Record\Collection
	 */
	public function getEvents() {
		return $this->getUrl()->load()->getContent(o('clean.singleAmpersand'))->getItems()->map(array($this, 'getEvent'));
	}

	/**
	 *
	 * @param Feed\Item $item
	 * @return \Type\Record 
	 */
	public function getEvent(Feed\Item $item) {
		$content = $item->url()->load(o('maxAge=7200'))->getContent();
		/* @var $content Xml */
		$description = $content->css('table.dsR13')->xpath('//tr[1]/td[2]')->asXml()->join('div')->formattedText()->normalizeParagraphs();
		$type = $item->title()->substringAfter('-')->substringBefore(':');
		return new \Type\Record(array(
				'title' => $item->title()->substringAfter('-')->substringAfter(':')->normalizeSpace(),
				'date' => $item->title()->substringBefore('-')->normalizeSpace()->asDate('%a, %d.%m.%Y %H:%M'),
				'location' => $this->getLocation(),
				'type' => $this->getTypeRepository()->findOneByTitle($type),
				'description' => $description,
				'url' => $item->url(),
				'proof' => $item->asXml(),
				'infodate' => $item->modifiedDate(),
			));
	}

}

?>
