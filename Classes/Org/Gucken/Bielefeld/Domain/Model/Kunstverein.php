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
class Kunstverein extends AbstractEventSource implements EventSourceInterface
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
        return $this->getUrl()->load('metadata.override.content-type=application/rss+xml')->getContent()
            ->getItems()->map(array($this, 'getEvent'));
    }

    /**
     *
     * @param \Type\Xml $xml
     * @return \Type\Record
     */
    public function getEvent(\Type\Feed\Item $item)
    {
        return new \Type\Record(
            array(
                'title' => $item->title()->normalizeSpace(),
                'date' => $item->modifiedDate(),
                'location' => $this->getLocation(),
                'type' => $this->getType(),
                'description' => $item->contentAsXml()->formattedText()->normalizeParagraphs(),
                'url' => $item->url(),
            )
        );
    }
}
