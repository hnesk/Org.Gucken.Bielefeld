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
class DeineEisbar extends AbstractEventSource implements EventSourceInterface {

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
		return $this->getUrl()->load()->getContent()->getItems()->map(array($this, 'getEvent'));
	}

	/**
	 *
	 * @param Feed\Item $item
	 * @return \Type\Record 
	 */
	public function getEvent(Feed\Item $item) {
		$title = $item->title()->substringAfter(' : ')->normalizeSpace();
		return new \Type\Record(array(
			'title' => $title,
			'date' => $item->title()->substringBefore(' : ')->asDate('%d %b %Y %H:%M'),
			'location' => $this->getLocation(),
			'description' => $item->content()->entityDecode(),
			'url' => $item->url(),
			'type' => $this->getTypeRepository()->findOneByKeywordString($title),
			'proof' => $item->asXml(),
		));
	}

}

?>
