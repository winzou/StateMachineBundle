<?php

namespace spec\winzou\Bundle\StateMachineBundle\Callback;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SM\Event\TransitionEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerAwareCallbackSpec extends ObjectBehavior
{
    function let(ContainerInterface $container)
    {
        $this->beConstructedWith(array(), array('@my_service', 'my_method'), $container);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('winzou\Bundle\StateMachineBundle\Callback\ContainerAwareCallback');
    }

    function it_converts_servicename_to_class($container, TransitionEvent $event, ContainerAwareCallbackSpec $service)
    {
        $this->beConstructedWith(array(), array('@my_service', 'dummy'), $container);

        $container->has('my_service')->shouldBeCalled()->willReturn(true);
        $container->get('my_service')->shouldBeCalled()->willReturn($service);

        $service->dummy($event)->shouldBeCalled()->willReturn(true);

        $this->call($event)->shouldReturn(true);
    }

    function it_does_not_convert_object($container, TransitionEvent $event, ContainerAwareCallbackSpec $service)
    {
        $this->beConstructedWith(array(), array($service, 'dummy'), $container);

        $service->dummy($event)->shouldBeCalled()->willReturn(true);

        $this->call($event)->shouldReturn(true);
    }

    function it_calls_invokable_services()
    {
        $this->beConstructedWith(array(), array('@my_service'), $container);

        $container->has('my_service')->willReturn(true);
        $container->get('my_service')->willReturn($service);

        $service->__invoke($event)->shouldBeCalled()->willReturn('I was invoked!');

        $this->call($event)->shouldReturn('I was invoked!');
    }

    public function __invoke()
    {
        return 'I was invoked!';
    }

    public function dummy()
    {
        return true;
    }
}
