<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use TYPO3\Flow\Annotations as Flow;
use Org\Gucken\Events\Annotations as Events;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource;
use Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;
use Org\Gucken\Events\Domain\Model\Location;

use Type\Url;
use Type\Xml;

/**
 * @Flow\Scope("prototype")
 */
class NeueSchmiede extends AbstractEventSource implements EventSourceInterface
{
    /**
     * @Events\Configurable
     * @var Location
     */
    protected $location;

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
     * @return \Type\Record\Collection
     */
    public function getEvents()
    {
        $options = o('clean.htmlEntityDecode')->merge(o('iconv.WINDOWS-1252=ISO-8859-1'));

        return $this->getUrl()->load('badHtml')->getContent($options)->css('#css_inhalt .boxwhite')->asXml()->map(
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
        $header = $xml->css('span.head_bold')->asString()->join();
        $type = $header->substringAfter('Uhr')->normalizeSpace();
        $title = $xml->css('.fliesstext b')->asString()->first()->normalizeSpace();

        return new \Type\Record(
            array(
                'title' => $title,
                'date' => $header->asDate('%d.%m.%Y\s+%H:%M'),
                'location' => $this->getLocation(),
                'type' => $this->getTypeRepository()->findOneByKeywordString($type . ' ' . $title),
                'short' => $type,
                'description' => $xml->css('div .fliesstext')->asString()->join("\n")->normalizeSpace()->substringAfter(
                    $title
                )->trim(),
                'proof' => $xml,
                'url' => $xml->getBaseUri()
            )
        );
    }
}
