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
class Kamp extends AbstractEventSource implements EventSourceInterface {

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
	 * @return \Org\Gucken\Events\Domain\Model\Location
	 */
	public function getLocation() {
		return $this->location;
	}

	/**
	 * @return \Type\Record\Collection
	 */
	public function getEvents() {
		return $this->getUrl()->load(o('metadata.override.content-type=application/rss+xml'))
			->getContent()->getItems()
			->map(array($this, 'getEvent'));
	}
	
    /**
     * @return \Type\Record\Collection
	 * @return \Type\Record 
     */
    public function getEvent(\Type\Feed\Item $item) {
		#$content = url($item->url(),$item->url()->getQueryVar('main')->replace(',', '?'))->load()->getContent()->xpath('//td[@bgcolor="#facdcf"]//td//td')->asXml()->first();

		/* @var $content \Type\Xml */
		return new \Type\Record(array(
			'title'         => $item->title()->substringAfter('-')->normalizeSpace(),
			'date'          => $item->createdDate(),
			'location'      => $this->getLocation(),
			'description'   =>  $item->description()->entityDecode()->fixPseudoWindows1252Encoding()->normalizeParagraphs(),
			'url'           =>  $item->url(),
		));
	}
}

?>
