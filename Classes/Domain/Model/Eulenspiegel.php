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
class Eulenspiegel extends AbstractEventSource implements EventSourceInterface {

	/**
	 * @Events\Configurable
	 * @var \Org\Gucken\Events\Domain\Model\Type
	 */
	protected $type;

	/**
	 * @Events\Configurable
	 * @var \Type\Url
	 */
	protected $linkUrl;

	/**
	 * @param string $linkUrl
	 */
	public function setLinkUrl($linkUrl) {
		$this->linkUrl = new Url($linkUrl);
	}

	/**
	 * @return Url
	 */
	public function getLinkUrl() {
		return $this->linkUrl;
	}

	/**
	 *
	 * @param \Org\Gucken\Events\Domain\Model\Type $type
	 */
	public function setType(\Org\Gucken\Events\Domain\Model\Type $type) {
		$this->type = $type;
	}

	/**
	 * @return \Org\Gucken\Events\Domain\Model\Type
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * @return \Type\Record\Collection
	 */
	public function getEvents() {
		$options = o('headers.X-Requested-With=XMLHttpRequest')->merge('metadata.override.content-type=application/json');
		return $this->getUrl()->load($options)->getContent()
				->jpath('content')->asHtml()
				->css('.buecherLine')->asXml()->map(array($this, 'getEvent'));
	}

	/**
	 *
	 * @param Xml $xml
	 * @return \Type\Record 
	 */
	public function getEvent(Xml $xml) {
		// cleanup recursive stacked xml
		$xml = $xml->xpath('./div[@class!="buecherLine"]')->asXml()->join('div')->semantify();
		$description = $xml->css('.inhalt')->asXml()->join()->formattedText();
		$data = array(
			'title' => $xml->css('.titel')->asString()->filter()->join(': ')->normalizeSpace(),
			'short_description' => $xml->css('.anlass')->asXml()->join()->formattedText(),
			'description' => $description,
			'url' => $this->getLinkUrl(),
			'type' => $this->getType(),
			'proof' => $xml
		);

		$dateAndLocation = $xml->css('.termindaten')->asString()->first();
		if ($dateAndLocation && $dateAndLocation->is()) {
			$data['date'] = $dateAndLocation->substringAfter('--')->asDate('%d.%m.%y\s*--\s*%H([.+]%M)?\s*Uhr');
			$data['location'] = $this->getLocationRepository()->findOneByKeywordString($dateAndLocation->substringBefore('--')->normalizeSpace());
		} else {
			$strongLines = $xml->css('.inhalt p strong')->asString()->join(' ');
			$data['date'] = $strongLines->asDate('%d\.\s*(%B|%m\.)\s*%Y\s+%H.%M');
			$data['location'] = $this->getLocationRepository()->findOneByKeywordString('Eulenspiegel');
		}
		return new \Type\Record($data);
	}

}

?>
