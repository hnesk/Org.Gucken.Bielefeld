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
class Forum extends AbstractEventSource implements EventSourceInterface {
            

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
        $self = $this;
        return $this->getUrl()->load('badHtml')->getContent()
            ->css('td.clSlideMenu1')
			->xpath('//table/tr/td/table/tr[2]/td/table/tr[td[2][@class="textwb"]]')
            ->asXml()->map(function (\Type\Xml $xml) use ($self){
				$hasImageSideBar = $xml->xpath('./following-sibling::tr[1]/td[1]/table')->asXml()->first();
				if ($hasImageSideBar) {
					return $self->convertEventWithConcertLayout($xml);
				} else {
					return $self->convertEventWithPartyLayout($xml);
				}
            });
    }
    
	/**
	 *
	 * @param \Type\Xml $xml 
	 */
	public function convertEventWithPartyLayout(Xml $xml) {
		$title = $xml->xpath('td[3]/text()[1]')->asString()->first();
		$date = $xml->xpath('./td[2]')->asString()->first();
		$short = $xml->xpath('td[3]/text()')->asString()->join("\n")->remove($title)->normalizeSpace();
		
		return new \Type\Record(array(
			'title' => $title->normalizeSpace(),
			'image' => $xml->xpath('td[3]/img/@src')->asUrl()->first(),
			'short' => $short,
			'date' => $date->append($short)->asDate('%a,\s*%d.%m.(.*?%H((\s*Uhr)|([.:]%M)))?','23:00'),
			'url' => $xml->getBaseUri(),
			'type' => $this->getType(),
			'location' => $this->getLocation(),			
		));
	} 
	
	public function convertEventWithConcertLayout(Xml $xml) {
		$title = $xml->xpath('td[3]/text()')->asString()->join(', ')->normalizeSpace();
		$date = $xml->xpath('./td[2]')->asString()->first();
		$infoLine = $xml->xpath('./following-sibling::tr[td[3][contains(text(),"Beginn")]][1]/td[3]')->asString()->first()->normalizeSpace();
		/* @var $infoLine \Type\String */
		
		$boxOffice = $infoLine->eachMatch('#(AK|Abendkasse)[\s:]+([\d,.-]+)#','$2')->first();
		$preSelling = $infoLine->eachMatch('#(VVK|Vorverkauf)[\s:]+([\d,.-]+)#','$2')->first();
		
		return new \Type\Record(array(
			'title' => $title->normalizeSpace(),
			'image' => $xml->xpath('./following-sibling::tr[1]/td[1]/table//img/@src')->asUrl()->first(),
			'short'		=> $infoLine,
			'description' => $xml->xpath('./following-sibling::tr[1]/td[@class="textw"]')->asXml()->first()->formattedText()->removeExcessiveEmptyLines(),
			'date' => $date->append($infoLine->substringAfter('Beginn:'))->asDate('%a,\s*%d.%m.(.*?%H((\s*Uhr)|([.:]%M)))?','21:00'),
			'url' => $xml->getBaseUri(),
			'cost_box_office' => is($boxOffice) ? $boxOffice->asNumber() : null,
			'cost_pre_selling' => is($preSelling) ? $preSelling->asNumber() : null,
			
			'type' => $this->getType(),
			'location' => $this->getLocation(),			
		));				
	} 
}

?>
