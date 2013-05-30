<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource,
	Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;
use Type\Feed;
use Org\Gucken\Events\Annotations as Events,
	TYPO3\Flow\Annotations as Flow;

/**
 * @Flow\Scope("prototype")
 */
class Kunstverein extends AbstractEventSource implements EventSourceInterface {

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
	 * @param \Org\Gucken\Events\Domain\Model\Type $type
	 */
	public function setType($type) {
		$this->type = $type;
	}

	/**
	 *
	 * @return \Org\Gucken\Events\Domain\Model\Type
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return \Type\Record\Collection
	 */
	public function getEvents() {
		return $this->getUrl()->load('metadata.override.content-type=application/rss+xml')->getContent()
				->getItems()->map(array($this, 'getEvent'));
	}

	/**
	 *
	 * @param \Type\Xml $xml
	 * @return \Type\Record 
	 */
	public function getEvent(\Type\Feed\Item $item) {
		return new \Type\Record(array(
				'title' => $item->title()->normalizeSpace(),
				'date' => $item->modifiedDate(),
				'location' => $this->getLocation(),
				'type' => $this->getType(),
				'description' => $item->contentAsXml()->formattedText()->normalizeParagraphs(),
				'url' => $item->url(),
			));
	}

}

?>
