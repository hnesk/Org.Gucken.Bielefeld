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
class Theaterlabor extends AbstractEventSource implements EventSourceInterface
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
     * @return \Type\Record\Collection
     */
    public function getEvents()
    {
        return $this->getUrl()->load('badhtml')->getContent()
            ->css("div#inhalt_gross table.contentpaneopen table tr")
            //->xpath('tr[string-length(normalize-space(td[2]//text()))>1]')
            ->asXml()->map(array($this, 'getEvent'));
    }

    /**
     *
     * @param \Type\Xml $tr
     * @return \Type\Record
     */
    public function getEvent(\Type\Xml $tr)
    {
        $short = $tr->xpath('td[4]')->toString()->trim()->append(', ')->append($tr->xpath('td[2]'))->normalizeSpace();
        $link = $tr->xpath('td[3]/a')->asUrl()->first();
        /* @var $link \Type\Url */
        $description = '';
        if (is($link) && !$link->getPath()->endsWith('.pdf') && $link->sameDomain($tr->getBaseUri())) {
            $description = $link->load('badhtml')->getContent()
                ->css('div#inhalt_gross table.contentpaneopen')->asXml()->item(1);

        }
        $date = $tr->xpath('td[1]')->asString()->normalizeSpace()->asDate('%d.%m.\s*%H[:.]%M');

        return !($date->is() && $date instanceof \Type\Date) ? null : new \Type\Record(
            array(
                'title' => $tr->xpath('td[3]')->asString()->normalizeSpace(),
                'date' => $date,
                'location' => $this->getLocation(),
                'type' => $this->getType(),
                'short' => $short,
                'description' => is($description) ? $description->formattedText()->normalizeParagraphs() : null,
                'image' => is($description) ? $description->css('img')->asUrl()->first() : null,
                'video' => is($description) ? $description->xpath('a[contains(@href, "youtube.com")]')->asUrl()->first() : null,
                'url' => $tr->getBaseUri(),
            )
        );
    }
}
