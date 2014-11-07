<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use TYPO3\Flow\Annotations as Flow;
use Org\Gucken\Events\Annotations as Events;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource;
use Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;
use Org\Gucken\Events\Domain\Model\Location;

use Type\Xml;


/**
 * @Flow\Scope("prototype")
 */
class Zweischlingen extends AbstractEventSource implements EventSourceInterface
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
     * @return \Type\Record\Collection
     */
    public function getEvents()
    {
        return $this->getUrl()->load('badHtml')->getContent()->css('table.rahmenwh')->asXml()->map(
            array($this, 'getEvent')
        );
    }

    /**
     *
     * @param Xml $table
     * @return \Type\Record
     */
    public function getEvent(Xml $table)
    {
        $wholeTitle = $table->css('td.ueberschrift1')->asString()->first()->normalizeSpace();
        $title = $wholeTitle->pregReplace('#^.+\d{4}\s+(.+)$#', '$1');
        $date = $wholeTitle->substringBefore($title);
        $time = $table->css('td.textsr')->asString()->first()->substringAfter('Beginn:')->normalizeSpace();
        $dateTime = $date->append(' ')->append($time)->normalizeSpace();

        $type = $table->css('td.textsb')->asString()->first()->normalizeSpace();

        $id = $table->css('td.ueberschrift1')->xpath('/a/@name')->asString()->first();

        return new \Type\Record(
            array(
                'title' => $title,
                'short' => $type,
                'description' => $table->css('td.texts')->asString()->normalizeParagraphs(),
                'date' => $dateTime->asDate('%d. %B %Y %H[:.]%M'),
                'type' => $this->typeRepository->findOneByKeywordString($type),
                'location' => $this->location,
                'url' => new \Type\Url(
                    $this->getUrl(),
                    '#' . $table->css('td.ueberschrift1')->xpath('/a/@name')->asString()->first()
                )
            )
        );
    }
}
