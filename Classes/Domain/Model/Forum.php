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
				
                $imageInText = $xml->xpath('./td[3]/img')->asXml()->first();

                if ($imageInText) {
                    return $self->getRecord(
                            $xml,
                            $imageInText->xpath('./preceding-sibling::text()'),
                            $xml->xpath('./following-sibling::tr[1]/td[2]'),
                            $xml->xpath('./td[3]/img'),
                            $imageInText->xpath('./following-sibling::text()')
                    );
                } else {
                    return $self->getRecord(
                            $xml,
                            $xml->xpath('./td[3]'),
                            $xml->xpath('./following-sibling::tr[td/@class="textwb"][1]'),
                            $xml->xpath('./following-sibling::tr[1]/td[1]//img'),
                            $xml->xpath('./following-sibling::tr[1]/td[2]')
                    );
                }
            });
    }
    
    public function getRecord($xml, $title, $time, $image, $description) {
        return new \Type\Record(array(
            'title'       => $title->asString()->join(' ')->normalizeSpace(),
            'time'        => $time->asString()->first() ? $time->asString()->first()->normalizeSpace() : '',
            'image'       => $image->asUrl()->first(),
            'description' => $description->asString()->join(' ')->normalizeSpace(),
            'type'        => $this->getType(),
            'date'        => $xml->xpath('./td[2]')->asString()->first()->asDate('%a,\s*%d.%m.','23:00'),            
            'location'    => $this->getLocation(),
            'url'         => $xml->getBaseUri(),
        ));
    }
	
}

?>
