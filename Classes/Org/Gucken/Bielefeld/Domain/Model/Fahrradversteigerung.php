<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use TYPO3\Flow\Annotations as Flow;
use Org\Gucken\Events\Annotations as Events;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource;
use Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;
use Org\Gucken\Events\Domain\Model\Location;
use Org\Gucken\Events\Domain\Model\Type;
use Type\Url;
use Type\Xml;


/**
 * @Flow\Scope("prototype")
 */
class Fahrradversteigerung extends AbstractEventSource implements EventSourceInterface
{


    /**
     * @Events\Configurable
     * @var Location
     */
    protected $location;

    /**
     * @Events\Configurable
     * @var Type
     */
    protected $type;


    /**
     * @param Location $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param Type $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @return \Type\Record\Collection
     */
    public function getEvents()
    {
        return $this->getUrl()->load('badHtml')->getContent()
            ->css('div.textblock table')->xpath('//tr[not(@class="bghblau")]')->asXml()->map(array($this, 'getEvent'));
    }

    /**
     *
     * @param \Type\Xml $xml
     * @return \Type\Record
     */
    public function getEvent(Xml $xml)
    {
        $td1 = $xml->xpath('td[1]')->asString()->normalizeSpace();
        $td2 = $xml->xpath('td[2]')->asString()->normalizeSpace();

        return new \Type\Record(
            array(
                'title' => 'Fahradversteigerung',
                'short' => $td2,
                'date' => $td1->append($td2)->asDate('%d.\s*%B\s*%Y\D+%H[:.]%M\s*Uhr'),
                'url' => $xml->getBaseUri(),
                'type' => $this->getType(),
                'location' => $this->getLocation(),
            )
        );
    }
}
