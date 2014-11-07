<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use TYPO3\Flow\Annotations as Flow;
use Org\Gucken\Events\Annotations as Events;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource;
use Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;
use Org\Gucken\Events\Domain\Model\Location;

use Type\Date;
use Type\Record;
use Type\Url;
use Type\Xml;

/**
 * @Flow\Scope("prototype")
 */
class Selje extends AbstractEventSource implements EventSourceInterface
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
        $url = $this->getUrl();
        $urls = \newdate()->getMonthRange(2)->map(
            function (Date $d) use ($url) {
                return $url->resolve($d->getMonthStringLong()->tokenize()->append('.html'));
            }
        );

        return $urls->load('badhtml')->getContent()
            ->css('#inhalt table.contenttable')->xpath('//tr[*[string-length(.) > 10]]')
            ->asXml()->map(array($this, 'getEvent'), '\Type\Record\Collection');
    }

    /**
     * @return \Type\Record\Collection
     */
    public function getEvent(\Type\Xml $xml)
    {
        $url = $xml->getBaseUri();

        if ($xml->css('td a')->asUrl()) {
            $detailUrl = $xml->css('td a')->asUrl();
            $detail = $detailUrl->load('badhtml')->getContent()->css('div#inhalt')->asXml();
            $title = $detail->css('h1.csc-firstHeader')->asString()->first();
            $description = $detail->css('p')->asXml()->join('div')->markdown();
            $image = $detail->xpath('.//a[@rel]')->asUrl()->first();
        }

        $title = $xml->xpath('./*[string-length(.) > 6]')->asString()->normalizeSpace();
        $dates = $xml->xpath('./*')->asString()->normalizeSpace()
            ->expandRanges(
                '#(?<from>\d+)\.\s*(?<op>[+-])\s*(?<to>\d+)\.\s*(\d+)\.\s+(\d+)[.:](\d+)#',
                '_item._4. _5:_6',
                ', '
            )
            ->eachMatch('#(\d+)\.(\d+)\.\s+(\d+).(\d+)#')
            ->asDate('%d.%m.\s+%H.%M');

        $type = $this->getTypeRepository()->findOneByKeywordString('Kinder');
        $location = $this->getLocation();

        return $dates->map(
            function (\Type\Date $date) use ($url, $title, $detailUrl, $title, $description, $image, $type, $location) {
                $data = array(
                    'url' => $url,
                    'date' => $date,
                    'title' => $title,
                    'detail_url' => $detailUrl,
                    'description' => $description,
                    'image' => $image,
                    'type' => $type,
                    'location' => $location
                );

                return new \Type\Record($data);
            }
        );
    }
}
