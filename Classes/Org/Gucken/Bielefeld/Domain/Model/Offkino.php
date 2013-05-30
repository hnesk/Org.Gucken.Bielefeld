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
class Offkino extends AbstractEventSource implements EventSourceInterface {
            

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
	 *
	 * @return \Type\Url\Collection
	 */
	public function getUrls() {
		return $this->getUrl()->load('badHtml')->getContent()
            ->css('div#component div.blog h2.contentheading a')->asUrl()		;
	}
		
    /**
     * @return \Type\Record\Collection
     */
    public function getEvents() {
        return $this->getUrls()->load('badHtml')->getContent()
            ->css('div#component')->asXml()->map(array($this, 'getEvent'));
	}
    
	/**
	 *
	 * @param \Type\Xml $xml
	 * @return \Type\Record 
	 */
	public function getEvent(Xml $xml) {
		$dateString = $xml->css('div.article-content h2')->asString()->normalizeSpace();		
		$content = $xml->css('div.article-content')->asXml()->formattedText(array('keep-images' => 'no'));
		
		return !is($dateString) ? null : new \Type\Record(array(
			'title' => $xml->css('h2.contentheading')->asString()->normalizeSpace(),
			'image' => $xml->css('img')->asUrl()->first(),
			'description' => $content->remove($dateString)->pregReplace('/---+/','')->normalizeParagraphs(),
			'date' => $dateString->asDate('%d.\s*%B','20:00'),
			'url' => $xml->getBaseUri(),
			'type' => $this->getType(),
			'location' => $this->getLocation(),			
		));
	} 
	
}
?>
