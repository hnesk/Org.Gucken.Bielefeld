<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource,
	Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;

use	Type\Xml;

use Org\Gucken\Events\Annotations as Events,
    TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @FLOW3\Scope("prototype")
 */
class Sams extends AbstractEventSource implements EventSourceInterface {

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
     *
     * @param \Org\Gucken\Events\Domain\Model\Type $type
     */
    public function setType(\Org\Gucken\Events\Domain\Model\Type $type) {
        $this->type = $type;
    }


    /**
     * @return \Type\Record\Collection
     */
    public function getEvents() {
		return $this->getUrl()->load('badHtml')->getContent()->css('#events .fullevent')->asXml()->map(array($this,'getEvent'));
    }
	
	/**
	 *
	 * @param Xml $xml
	 * @return \Type\Record 
	 */
	public function getEvent(Xml $xml) {
		#$xml->debug();
		$wholeTitle = $xml->css('.eventinfo a h2')->asString()->normalizeSpace();
		$title = $wholeTitle->pregReplace('/^(?:\d+\|\d+\s+)?(.+)\[ab\s\d+h\]$/', '$1');		
		$date = $wholeTitle->substringBefore($title);
		#if (!is($date)) {
			$date = $xml->getDocument()->xpath('//a[@href="#'.$xml->getAttribute('id').'"]')->css('h3')->asString()->asDate('%d/%m')->modified('-1 day')->strftime('%d|%m');
		#}
		$dateTimeString = $date->append(' ')->append($wholeTitle->substringAfter($title))->normalizeSpace();		
		
		return new \Type\Record(array(
			'title' => $title,
			'date' => $dateTimeString->asDate('%d[\|/]%m.+\[ab\s%Hh\]', null, 0 , 5),
			'end' => $xml->css('.eventlogo div span')->asString()->normalizeSpace()->asDate('%d.%m.%Y\s+%H:%M'),
			'description' => $xml->css('.eventinfo')->xpath('/text()')->asString()->normalizeParagraphs(),
			'type' => $this->type,
			'location' => $this->location,
		));
	}
}

?>
