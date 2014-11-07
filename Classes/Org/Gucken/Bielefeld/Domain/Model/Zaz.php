<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use TYPO3\Flow\Annotations as Flow;
use Org\Gucken\Events\Annotations as Events;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource;
use Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;

use Type\Feed\Item;
use Type\Url;
use Type\Xml;


/**
 * @Flow\Scope("prototype")
 */
class Zaz extends AbstractEventSource implements EventSourceInterface
{

    /**
     * @return \Type\Record\Collection
     */
    public function getEvents()
    {
        $options = o('clean.singleAmpersand')
            ->merge(o('clean.htmlEntityDecode'))
            ->merge(o('clean.nonXmlChars=#'))
            ->merge(o('iconv.WINDOWS-1252=ISO-8859-1'));

        return $this->getUrl()->load()->getContent($options)->getItems()->map(array($this, 'getEvent'));
    }

    /**
     *
     * @param Item $item
     * @return \Type\Record
     */
    public function getEvent(Item $item)
    {
        $location = $item->title()->substringAfter('@')->normalizeSpace();
        $description = $item->content()->normalizeParagraphs();

        return new \Type\Record(
            array(
                'title' => $item->title()->substringAfter(',')->substringBefore('@')->normalizeSpace(),
                'date' => $item->title()->substringBefore(',')->asDate('%d.%m.%y %H:%M'),
                'location' => $this->getLocationRepository()->findOneByKeywordString($location),
                'type' => $this->getTypeRepository()->findOneByKeywordString(
                    $item->title() . ' ' . $description->substring(0, 100)
                ),
                'description' => $description,
                'url' => $item->url(),
                'proof' => $item->asXml()
            )
        );
    }

}

?>