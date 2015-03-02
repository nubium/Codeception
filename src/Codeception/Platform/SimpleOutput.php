<?php
namespace Codeception\Platform;

use Codeception\Event\FailEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Events;
use Codeception\Event\TestEvent;

class SimpleOutput extends Extension
{

    /**
     * @var int
     */
    protected $counter = 0;

    /**
     * @var FailEvent
     */
    protected $lastFail;



    public function _initialize()
    {
        $this->options['silent'] = false; // turn on printing for this extension
        $this->_reconfigure(['settings' => ['silent' => true]]); // turn off printing for everything else
    }

    // we are listening for events
    static $events = array(
        Events::SUITE_BEFORE => 'beforeSuite',
        Events::TEST_END     => 'after',
        Events::TEST_SUCCESS => 'success',
        Events::TEST_FAIL    => 'fail',
        Events::TEST_ERROR   => 'error',
    );

    public function beforeSuite(SuiteEvent $event)
    {
        $this->counter = 0;
        $this->writeln('');
        $this->writeln($event->getSuite()->getName().' (tests: '.count($event->getSuite()->tests()).')');
    }

    public function success()
    {
        $this->write('[+] '.$this->counter++.'. ');
        $this->lastFail = NULL;
    }

    public function fail(FailEvent $event)
    {
        $this->write('[-] '.$this->counter++.'. ');
        $this->lastFail = $event;
    }

    public function error(FailEvent $event)
    {
        $this->write('[E] '.$this->counter++.'. ');
        $this->lastFail = $event;
    }

    // we are printing test status and time taken
    public function after(TestEvent $e)
    {
        $seconds_input = $e->getTime();
        // stack overflow: http://stackoverflow.com/questions/16825240/how-to-convert-microtime-to-hhmmssuu
        $seconds = (int)($milliseconds = (int)($seconds_input * 1000)) / 1000;
        $time    = ($seconds % 60) . (($milliseconds === 0) ? '' : '.' . $milliseconds);

        $this->write($e->getTest()->toString());
        $this->writeln(' (' . $time . 's)');

        if ($this->lastFail) {
            $this->writeln('    '.$this->lastFail->getFail()->getMessage());
        }
    }
}
