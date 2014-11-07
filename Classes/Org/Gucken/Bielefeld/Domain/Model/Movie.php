<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use TYPO3\Flow\Annotations as Flow;
use Org\Gucken\Events\Annotations as Events;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource;
use Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;
use Org\Gucken\Events\Domain\Model\Location;
use Org\Gucken\Events\Domain\Model\Type;

use Type\Feed;

/**
 * @Flow\Scope("prototype")
 */
class Movie extends AbstractEventSource implements EventSourceInterface
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
     *
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
     *
     * @return Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     *
     * @return \Type\Url\Collection
     */
    public function getUrls()
    {
        return $this->getUrl()->load()->getContent()->css('#spalteBreit div.eventText a')->asUrl();
    }

    /**
     * @return \Type\Record\Collection
     */
    public function getEvents()
    {
        return $this->getUrls()->load('badhtml')
            ->getContent()->css('#spalteBreit div.eventContainer')
            ->asXml()->map(array($this, 'getEvent'));
    }

    /**
     *
     * @param \Type\Xml $xml
     * @return \Type\Record
     */
    public function getEvent(\Type\Xml $xml)
    {
        $infoHeader = $xml->xpath('div[@id="ticketmaster-categoryheader"][contains(.,"Event-Infos")]');
        $xmlDescription = $infoHeader->xpath('/following-sibling::p')->asXml();
        $description = $xmlDescription->is() ? $xmlDescription->join()->formattedText() : $infoHeader->xpath(
            '/following-sibling::text()'
        )->asString()->join();

        return new \Type\Record(
            array(
                'title' => $xml->xpath('table//tr[1]/td[3]')->asString()->normalizeSpace(),
                'date' => $xml->xpath('table//tr[2]/td[3] | table//tr[3]/td[3]')->asString()->join(' ')->normalizeSpace(
                )->asDate('%d.%m.%Y\s+%H(:%M)?'),
                'location' => $this->getLocation(),
                'type' => $this->getType(),
                'description' => $description->normalizeParagraphs(),
                'url' => $xml->getBaseUri(),
                'cost_box_office' => $xml->xpath('.//table//tr[5]/td[3]')->asString()->normalizeSpace()->asNumber()
            )
        );
    }
}
