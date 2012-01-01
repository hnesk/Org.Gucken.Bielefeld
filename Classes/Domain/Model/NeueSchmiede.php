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
class NeueSchmiede extends AbstractEventSource implements EventSourceInterface {
            

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
        $self = $this;
		$options = o('clean.htmlEntityDecode')->merge(o('iconv.WINDOWS-1252=ISO-8859-1'));

        return $this->getUrl()->load('badHtml')->getContent($options)->css('#css_inhalt .boxwhite')->asXml()->map(function (Xml $xml) use ($self) {
				$header = $xml->css('span.head_bold')->asString()->join();
				$type  = $header->substringAfter('Uhr')->normalizeSpace();
				$title = $xml->css('.fliesstext b')->asString()->first()->normalizeSpace();
                return new \Type\Record(array(
                    'title'         => $title,
                    'date'          => $header->asDate('%d.%m.%Y\s+%H:%M'),
                    'location'      => $self->getLocation(),
                    'type'          => $self->getTypeRepository()->findOneByKeywordString($type.' '.$title),
					'short'			=> $type,
                    'description'   => $xml->css('div .fliesstext')->asString()->join("\n")->normalizeSpace()->substringAfter($title)->trim(),
                    'proof'         => $xml,
                    'url'           => $xml->getBaseUri()                    
                ));
            }
        );
    }
}

?>
