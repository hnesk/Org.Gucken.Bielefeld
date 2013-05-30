<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource,
	Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;
use Type\Url,
	Type\Xml,
	Type\Date,
	Type\Record;
use Org\Gucken\Events\Annotations as Events,
	TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @FLOW3\Scope("prototype")
 */
class Niekamp extends AbstractEventSource implements EventSourceInterface {

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
	 * @Events\Configurable
	 * @var string
	 */
	protected $linkText;
	
	/**
	 * @Events\Configurable
	 * @var string
	 */
	protected $defaultTime;

	
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
	 *
	 * @param string $linkText 
	 */
	public function setLinkText($linkText) {
		$this->linkText = $linkText;
	}
	
	/**
	 *
	 * @param string $defaultTime 
	 */
	public function setDefaultTime($defaultTime) {
		$this->defaultTime = $defaultTime;
	}

		
	/**
	 *
	 * @return \Type\Url
	 */
	public function getTypeUrl() {
		return $this->getUrl()->load()->getContent()->css('.navi_left a:contains("'.$this->linkText.'")')->asUrl();
	}

	/**
	 * @return \Type\Record\Collection
	 */
	public function getEvents() {
        return $this->getTypeUrl()->load('badhtml')->getContent()
			->css('table.listing tr td')->xpath('//a[@href][normalize-space(.//text())]')
            ->asXml()->map(array($this,'getEvent'));
	}

	/**
	 * @return \Type\Record\Collection
	 */
	public function getEvent(\Type\Xml $xml) {	
		$url   = $xml->xpath('./@href')->asUrl()->first();

		$day   = $xml->text()->substringBefore('_')->normalizeSpace();
		$month = $xml->xpath('./ancestor::tr | ./ancestor::tr/preceding-sibling::tr')->asXml()
					->xpath('./td[@class="months"][string-length(.) > 2][1]')->asString()->last()->normalizeSpace();
		$year  = $xml->xpath('./preceding::h2[1]')->asString()->last()->normalizeSpace();
		$time  = $xml->text()->substringAfter('_')->substringBefore('UHR:')->normalizeSpace()->defaultTo($this->defaultTime);
		$date  = \Type\String\Factory::fromFormat('%d. %s %d %s', $day, $month, $year, $time)->asDate('%d. %B %Y %H:%M');

		$details = $url->load()->getContent()->css('div.content_right_bottom')->asXml();
		
		return new \Type\Record(array(
			'title' => $xml->text()->substringAfter('_')->substringAfter('UHR: ',true)->normalizeSpace(),
			'url' => $url,
			'date' => $date,
			'description' => $details->css('p')->asString()->slice(0,2)->join(PHP_EOL.PHP_EOL)->normalizeParagraphs(),
			'location' => $this->location,
			'type' => $this->type
		));
		
	}

}

?>
