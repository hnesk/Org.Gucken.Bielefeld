<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use TYPO3\Flow\Annotations as Flow;
use Org\Gucken\Events\Annotations as Events;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource;
use Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;
use Org\Gucken\Events\Domain\Model\Location;
use Org\Gucken\Events\Domain\Model\Type;

use Type\Xml;

/**
 * @Flow\Scope("prototype")
 */
class TrotzAlledemTheater extends AbstractEventSource implements EventSourceInterface
{

    /**
     * @Events\Configurable
     * @var string
     */
    protected $url = 'http://www.trotz-alledem-theater.de/scr/termine.phtml?trm=1';

    /**
     * @Events\Configurable
     * @var Type
     */
    protected $type;

    /**
     * @Events\Configurable
     * @var Location
     */
    protected $location;

    /**
     *
     * @param Type $type
     */
    public function setType(Type $type)
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
     * @param Location $location
     */
    public function setLocation(Location $location)
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
        return $this->getUrl()->load('badHtml')->getContent()
            ->css('td#trmsp2 table')->xpath('//tr[td/div[@id="trmhdl"]]')->asXml()->map(array($this, 'getEvent'));
    }

    /**
     *
     * @param Xml $xml
     * @return \Type\Record
     */
    public function getEvent(Xml $xml)
    {

        $href = $xml->xpath('.//a[@id="trmmnu3"]/@href')->asString()->first();
        $id = $href->replace('javascript:STKopen:', '')->trim("()'");
        $url = url('http://www.trotz-alledem-theater.de/scr/stueck.phtml')->resolve('?stk=' . $id);

        return new \Type\Record(
            array(
                'title' => $xml->css('a#trmmnu3')->asString()->normalizeSpace()->append($href),
                'date' => \Type\Date::ago(1),
                #'date' => $xml->css('#trmstk')->asString()->normalizeSpace()->asDate('%d.%m.\s*%H.%M\sUhr'),
                'location' => $this->getLocation(),
                'type' => $this->getType(),
                'description' => $xml->css('#trmstk')->asString()->slice(1, 1)->normalizeParagraphs(),
                'proof' => $xml,
                'url' => $url
            )
        );
    }
}
