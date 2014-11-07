<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use TYPO3\Flow\Annotations as Flow;
use Org\Gucken\Events\Annotations as Events;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource;
use Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;
use Org\Gucken\Events\Domain\Model\Location;
use Org\Gucken\Events\Domain\Model\Type;

use Type\Date;
use Type\Record;
use Type\Url;
use Type\Xml;

/**
 * @Flow\Scope("prototype")
 */
class Ostbahnhof extends AbstractEventSource implements EventSourceInterface
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
     * @param Type $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }


    /**
     * @return \Type\Record\Collection
     */
    public function getEvents()
    {
        return $this->getUrl()->load('badhtml')->getContent()
            ->css('div#left_col_con div.ev_tease a')->asUrl()->load('badhtml')->getContent()
            ->css('div#right_col_con')->asXml()
            ->map(array($this, 'getEvent'));
    }

    /**
     * @return \Type\Record\Collection
     */
    public function getEvent(\Type\Xml $xml)
    {
        $title = $xml->css('.event_detail_headline')->asString()->normalizeSpace();

        return $title->contains('Geschlossen') ? null : new \Type\Record(
            array(
                'date' => $xml->css('.event_detail_date')->asString()->normalizeSpace()->asDate('%d.%m.%Y - %H[:.]%M'),
                'title' => $xml->css('.event_detail_headline')->asString()->normalizeSpace(),
                'image' => $xml->css('.event_detail_img img')->asUrl()->first(),
                'description' => $xml->css('.event_detail_text')->asXml()->join()->markdown(),
                'type' => $this->type,
                'location' => $this->location,
                'url' => $xml->getBaseUri()
            )
        );
    }
}
