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
class Stereo extends AbstractEventSource implements EventSourceInterface
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
            ->css('#maininhalt div.inhaltwidth3>table tr')->asXml()
            ->map(array($this, 'getEvent'));
    }

    /**
     *
     * @param \Type\Xml $xml
     * @return \Type\Record
     */
    public function getEvent(Xml $xml)
    {
        $moreUrl = $xml->xpath('td[3]')->css('a.linkliste')->asUrl()->first();
        /* @var $moreUrl Url */
        $moreXml = $moreUrl->trim(\Type\Url::NOFRAGMENT)->loadBadHtml()->getContent();
        /* @var $moreXml Xml */

        $description = $moreXml->fragment($moreUrl->getFragment())
            ->xpath('./following-sibling::div[1]//div[contains(@class,"textbox1")]')
            ->asXml()->slice(2)->join('div')->formattedText();
        $description = $description->remove('[]')->normalizeParagraphs();

        /** @todo evtl über die veranstaltungen loopen ? */

        return new \Type\Record(
            array(
                'title' => $xml->xpath('td[3]')->css('.linkliste')->asString()->first(),
                // @todo ist noch halbgar: uhrzeit ziehen
                'date' => $xml->xpath('td[2]')->asString()->first()->normalizeSpace()->asDate('%d.%m.%y'),
                'description' => $description,
                'url' => $moreUrl,
                'location' => $this->getLocation(),
                'type' => $this->getType(),
                #'proof' => $xml
            )
        );
    }
}
