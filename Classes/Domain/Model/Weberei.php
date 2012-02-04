<?php

namespace Org\Gucken\Bielefeld\Domain\Model;

use Org\Gucken\Events\Domain\Model\EventSource\AbstractEventSource,
	Org\Gucken\Events\Domain\Model\EventSource\EventSourceInterface;
use Type\Url,
	Type\Xml,
	Type\Date,
	Type\Record,
	Util\Lookup,
	Util\Lookup\Source;
use Org\Gucken\Events\Annotations as Events,
	TYPO3\FLOW3\Annotations as FLOW3;

/**
 * @FLOW3\Scope("prototype")
 */
class Weberei extends AbstractEventSource implements EventSourceInterface {

	/**
	 * @Events\Configurable
	 * @var \Org\Gucken\Events\Domain\Model\Location
	 */
	protected $location;

	/**
	 *
	 * @var Lookup
	 */
	protected $websiteEvents;

	
	public function __construct() {
		parent::__construct();
		$this->prepareWebsiteEvents();
	}
	
	/**
	 * @param \Org\Gucken\Events\Domain\Model\Location $location
	 */
	public function setLocation($location) {
		$this->location = $location;
	}

	/**
	 * @return \Org\Gucken\Events\Domain\Model\Location
	 */
	public function getLocation() {
		return $this->location;
	}

	
	/**
	 *
	 * @return void
	 */
	protected function prepareWebsiteEvents() {
		$urlCollection = \newdate()->getMonthRange(3)->map(function (\Type\Date $d) {
			return \url('http://www.die-weberei.de/index.html?site=PROGRAMM&suchdat=%s')->sprintf($d->strftime('%d.%m.%Y'));
		});
		$websiteEventCollection = $urlCollection->load()->getContent('iconv.Windows-1252=ISO-8859-1')
			->css('#content_spalte_links #Programm1')->asXml()
			->map(function (Xml $xml) {
				$text = $xml->css('#Programm_Text')->asXml()->asString()->first();
				return new Record(array(
					'id' => $xml->xpath('.//plusone/@href')->asUrl()->first()->getQueryVar('eventid'),
					'starttime' => $text->asDate('(Einlass|Beginn)\s*:</b>\s*%H:%m\s*Uhr'),
					'endtime' => $text->asDate('Ende</b>\s*%H:%m\s*Uhr'),
					'description' => $xml->css('.PRO_Admintool_accordionContent')->asXml()->first()->formattedText()->normalizeParagraphs(),
				));
			});

		$this->websiteEvents = new Lookup(new Source\RecordCollection($websiteEventCollection, 'id'));
	}

	/**
	 *
	 * @param Url $url
	 * @return Type\Record
	 */
	public function getWebsiteEventByUrl(Url $url) {
		return $this->websiteEvents->lookupRecord($url->getQueryVar('eventid'));
	}

	/**
	 * @return \Type\Record\Collection
	 */
	public function getEvents() {
		return $this->getUrl()->load('metadata.override.content-type=application/rss+xml')
				->getContent()
				->getItems()
				->map(array($this, 'getEvent'));
	}

	/**
	 * @return \Type\Record\Collection
	 */
	public function getEvent(\Type\Feed\Item $item) {
		$websiteEvent = $this->getWebsiteEventByUrl($item->url());
		/* @var $websiteEvent Record */

		if ($websiteEvent && $websiteEvent->is()) {
			$title = $item->title()->substringAfter(' ');
			$date = $item->title()->substringBefore(' ')->asDate('%d.%m.%Y');
			$startDate = $date->timed($websiteEvent->get('starttime'));
			$type = $title->find('Tanzbares,Flohmarkt,Gastronomie Event,Kabarett und Comedy,Lesung / Vortrag,Open Air,Tagung / Workshop,Theater,Live on Stage');
			$title = $title->normalizeSpace()->pregReplace('#' . $type . '$#i', '');

			$event = array(
				'title' => $title,
				'date' => $startDate,
				'type' => $this->typeRepository->findOneByKeywordString($type),
				'description' => $websiteEvent->get('description'),
				'url' => $item->url(),
				'image' => $item->asXml()->css('bild')->asUrl()->first(),
				'location' => $this->getLocation()
			);

			if ($websiteEvent->get('endtime') && $websiteEvent->get('endtime')->is()) {
				$event['enddate'] = $date->timed($websiteEvent->get('endtime'))->guaranteeAfter($startDate);
			}
			
			
			
			return $title->toLower()->contains('geschlossen') ? null : new \Type\Record($event);
		}
	}

}

?>
