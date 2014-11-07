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
class Offkino extends AbstractEventSource implements EventSourceInterface
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
     *
     * @return \Type\Url\Collection
     */
    public function getUrls()
    {
        return $this->getUrl()->load('badHtml')->getContent()
            ->css('div#component div.blog h2.contentheading a')->asUrl();
    }

    /**
     * @return \Type\Record\Collection
     */
    public function getEvents()
    {
        return $this->getUrls()->load('badHtml')->getContent()
            ->css('div#component')->asXml()->map(array($this, 'getEvent'));
    }

    /**
     *
     * @param \Type\Xml $xml
     * @return \Type\Record
     */
    public function getEvent(Xml $xml)
    {
        $dateString = $xml->css('div.article-content h2')->asString()->normalizeSpace();
        $content = $xml->css('div.article-content')->asXml()->formattedText(array('keep-images' => 'no'));

        return !is($dateString) ? null : new \Type\Record(
            array(
                'title' => $xml->css('h2.contentheading')->asString()->normalizeSpace(),
                'image' => $xml->css('img')->asUrl()->first(),
                'description' => $content->remove($dateString)->pregReplace('/---+/', '')->normalizeParagraphs(),
                'date' => $dateString->asDate('%d.\s*%B', '20:00'),
                'url' => $xml->getBaseUri(),
                'type' => $this->getType(),
                'location' => $this->getLocation(),
            )
        );
    }
}
