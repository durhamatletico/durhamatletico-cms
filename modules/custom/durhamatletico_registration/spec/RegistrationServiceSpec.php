<?php

namespace spec;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RegistrationServiceSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('RegistrationService');
    }
}
