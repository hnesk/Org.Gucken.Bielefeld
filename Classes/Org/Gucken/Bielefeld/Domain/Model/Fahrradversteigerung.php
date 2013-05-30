<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource,
	Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;

use Type\Url,
    Type\Xml;

use Org\Gucken\Events\Annotations as Events,
    TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @FLOW3\Scope("prototype")
 */
class Fahrradversteigerung extends AbstractEventSource implements EventSourceInterface {
            

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
            ->css('div.textblock table')->xpath('//tr[not(@class="bghblau")]')->asXml()->map(array($this, 'getEvent'));
	}
    
	/**
	 *
	 * @param \Type\Xml $xml
	 * @return \Type\Record 
	 */
	public function getEvent(Xml $xml) {
		$td1 = $xml->xpath('td[1]')->asString()->normalizeSpace();
		$td2 = $xml->xpath('td[2]')->asString()->normalizeSpace();
		
		return new \Type\Record(array(
			'title' => 'Fahradversteigerung',
			'short' => $td2,
			'date' => $td1->append($td2)->asDate('%d.\s*%B\s*%Y\D+%H[:.]%M\s*Uhr'),
			'url' => $xml->getBaseUri(),
			'type' => $this->getType(),
			'location' => $this->getLocation(),			
		));
	} 
	
}
?>
