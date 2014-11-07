<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use TYPO3\Flow\Annotations as Flow;
use Org\Gucken\Events\Annotations as Events;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource;
use Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;
use Org\Gucken\Events\Domain\Model\Location;

use Type\Feed;
use Type\Xml;


/**
 * @Flow\Scope("prototype")
 */
class Falkendom extends AbstractEventSource implements EventSourceInterface
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
        return $this->getUrl()->load()->getContent(o('clean.singleAmpersand'))->getItems()->map(
            array($this, 'getEvent')
        );
    }

    /**
     *
     * @param Feed\Item $item
     * @return \Type\Record
     */
    public function getEvent(Feed\Item $item)
    {
        $content = $item->url()->load(o('maxAge=7200'))->getContent();
        /* @var $content Xml */
        $description = $content->css('table.dsR13')->xpath('//tr[1]/td[2]')->asXml()->join('div')->formattedText(
        )->normalizeParagraphs()->replace('Share [http://www.facebook.com/sharer.php]', '');
        $type = $item->title()->substringAfter('-')->substringBefore(':');

        return new \Type\Record(
            array(
                'title' => $item->title()->substringAfter('-')->substringAfter(':')->normalizeSpace(),
                'date' => $item->title()->substringBefore('-')->normalizeSpace()->asDate('%a, %d.%m.%Y %H:%M'),
                'location' => $this->getLocation(),
                'type' => $this->getTypeRepository()->findOneByTitle($type),
                'description' => $description,
                'url' => $item->url(),
                'proof' => $item->asXml(),
                'infodate' => $item->modifiedDate(),
            )
        );
    }
}
