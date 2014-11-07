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
class DeineEisbar extends AbstractEventSource implements EventSourceInterface
{

    /**
     * @Events\Configurable
     * @var Location
     */
    protected $location;

    /**
     * @param Location $location
     */
    public function setLocation(Location $location)
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
        return $this->getUrl()->load()->getContent()->getItems()->map(array($this, 'getEvent'));
    }

    /**
     *
     * @param Feed\Item $item
     * @return \Type\Record
     */
    public function getEvent(Feed\Item $item)
    {
        $title = $item->title()->substringAfter(' : ')->normalizeSpace();

        return new \Type\Record(
            array(
                'title' => $title,
                'date' => $item->title()->substringBefore(' : ')->asDate('%d %b %Y %H:%M'),
                'location' => $this->getLocation(),
                'description' => $item->content()->entityDecode(),
                'url' => $item->url(),
                'type' => $this->getTypeRepository()->findOneByKeywordString($title),
                'proof' => $item->asXml(),
            )
        );
    }
}
