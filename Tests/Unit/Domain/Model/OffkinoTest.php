<?php
namespace Org\Gucken\Bielefeld\Tests\Unit\Domain\Model;

use Org\Gucken\Bielefeld\Domain\Model\Offkino;
use Org\Gucken\Events\Tests\EventSourceUnitTestCase;

require_once FLOW_PATH_PACKAGES . '/Application/Org.Gucken.Events/Tests/EventSourceUnitTestCase.php';

class OffkinoTest extends EventSourceUnitTestCase
{

    public function setUp()
    {
        $this->baseUrl = 'file://' . realpath(__DIR__ . '/../../Fixtures');
        $this->source = new Offkino();
    }

    public function tearDown()
    {
        $this->baseUrl = '';
        $this->source = null;
    }

    /**
     * @test
     * @dataProvider getData
     */
    public function eventsHaveCorrectData($file, $nr, $data)
    {

        $this->markTestIncomplete('cant follow local urls yet');
        #return $this->assertEventDataIsCorrect($file, $nr, $data);
    }


    public static function getData()
    {
        return array(
            array(
                'Offkino/Programm20120114.html',
                0,
                array(
                    'title' => 'Lebenszeichen',
                    'image' => 'http://images/stories/lebenszeichen.jpg',
                    'date' => new \DateTime('2011-12-16T20:00'),
                    'short' => 'Einlass: 19 Uhr Beginn:20 Uhr VVK: 16,-'
                )
            ),
        );
    }
}
