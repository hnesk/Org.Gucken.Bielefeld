<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use TYPO3\Flow\Annotations as Flow;
use Org\Gucken\Events\Annotations as Events;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource;
use Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;
use Org\Gucken\Events\Domain\Model\Location;

use Type\Feed;


/**
 * @Flow\Scope("prototype")
 */
class Kunsthalle extends AbstractEventSource implements EventSourceInterface
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
     *
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
        return $this->getUrl()->load('badhtml')->getContent()
            ->css('div.event_catmenu select')->xpath('/option/@value')->asUrl()
            ->filter(
                function (\Type\Url $url) {
                    $categoryId = (string)$url->getQueryObject()->jpath('tx_ttnews/cat');

                    // 26 = Führungen, viel zu viele und nicht so interessant
                    return $categoryId && $categoryId != '26';
                }
            )
            ->load('badhtml')->getContent()
            ->css('#content div.event')->asXml()->map(array($this, 'getEvent'));
    }

    /**
     *
     * @param \Type\Xml $xml
     * @return \Type\Record
     */
    public function getEvent(\Type\Xml $xml)
    {
        $short = $xml->css('div.col_right p')->xpath('/preceding-sibling::text()')->asString()->normalizeSpace();
        $title = pick($xml->css('div.col_right>strong')->asString()->normalizeSpace(), $short);
        // @TODO: mehrere Types
        $typeString = $short->equals('Groß und Klein') || $short->equals('Familienzeit!') ? 'Kinder' : 'Kunst';

        return new \Type\Record(
            array(
                'title' => $title,
                'short' => $short,
                'date' => $xml->css('div.col_left')->asString()->normalizeSpace()->asDate('%d\|%m\D+%H:%M'),
                'enddate' => $xml->css('div.col_left')->asString()->normalizeSpace()->asDate('%d\|%m[^-]+-\s*%H:%M'),
                'description' => $xml->css('div.col_right p')->asXml()->join('div')->formattedText(),
                'type' => $this->getTypeRepository()->findOneByKeywordString($typeString),
                'location' => $this->getLocation(),
                'url' => $xml->getBaseUri(),
            )
        );
    }
}
