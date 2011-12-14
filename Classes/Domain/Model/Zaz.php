<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource,
	Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;

use Type\Url,
    Type\Xml;

use Org\Gucken\Events\Annotations as Events,
    TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @FLOW3\Scope("prototype")
 */
class Zaz extends AbstractEventSource implements EventSourceInterface {
           

    
    /**
     * @return \Type\Record\Collection
     */
    public function getEvents() {
        $self = $this;
		$options = o('clean.singleAmpersand')
			->merge(o('clean.htmlEntityDecode'))
			->merge(o('iconv.WINDOWS-1252=ISO-8859-1'))
		;
		
        $feed = $this->getUrl()->load()->getContent($options);
        /* @var $feed \Type\Feed */
        return $feed->getItems()->map(function(\Type\Feed\Item $item) use ($self) {
            $location = $item->title()->substringAfter('@')->normalizeSpace();
            return new \Type\Record(array(
                'title'         => $item->title()->substringAfter(',')->substringBefore('@')->normalizeSpace(),
                'date'          => $item->title()->substringBefore(',')->asDate('%d.%m.%y %H:%M'),
                'location'      => $self->getLocationRepository()->findOneByKeywordString($location),
                'type'          => $self->getTypeRepository()->findOneByKeywordString($item->title()),
                'description'   => $item->content(),
                'url'           => $item->url(),
                'proof'         => $item->asXml()
            ));
        }, '\Type\Record\Collection');
    }    

}

?>
