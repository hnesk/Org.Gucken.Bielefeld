<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use TYPO3\Flow\Annotations as Flow;
use Org\Gucken\Events\Annotations as Events;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource;
use Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;
use Org\Gucken\Events\Domain\Model\Location;
use Org\Gucken\Events\Domain\Model\Type;

use Type\Xml;


/**
 * @Flow\Scope("prototype")
 */
class Sams extends AbstractEventSource implements EventSourceInterface
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
    public function setType(Type $type)
    {
        $this->type = $type;
    }


    /**
     * @return \Type\Record\Collection
     */
    public function getEvents()
    {
        return $this->getUrl()->load('badHtml')->getContent()->css('#events .fullevent')->asXml()->map(
            array($this, 'getEvent')
        );
    }

    /**
     *
     * @param Xml $xml
     * @return \Type\Record
     */
    public function getEvent(Xml $xml)
    {
        $wholeTitle = $xml->css('.eventinfo a h2')->asString()->normalizeSpace();
        $title = $wholeTitle->pregReplace('/^(?:\d+\|\d+\s+)?(.+)\[ab\s\d+h\]$/', '$1');
        $date = $xml->getDocument()
            ->xpath('//a[@href="#' . $xml->getAttribute('id') . '"]')
            ->css('h3')->asString()
            ->asDate('%d/%m')->modified('-1 day')->strftime('%d|%m');
        $dateTimeString = $date->append(' ')->append($wholeTitle->substringAfter($title))->normalizeSpace();

        return new \Type\Record(
            array(
                'title' => $title,
                'date' => $dateTimeString->asDate('%d[\|/]%m.+\[ab\s%Hh\]', null, 0, 5),
                'end' => $xml->css('.eventlogo div span')->asString()->normalizeSpace()->asDate('%d.%m.%Y\s+%H:%M'),
                'description' => $xml->css('.eventinfo')->xpath('/text()')->asString()->normalizeParagraphs(),
                'type' => $this->type,
                'location' => $this->location,
            )
        );
    }
}
