<?php

namespace spec\winzou\Bundle\StateMachineBundle\Callback;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SM\Event\TransitionEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

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

        $container->has('my_service')->willReturn(true);
        $container->get('my_service')->shouldBeCalled()->willReturn($service);

        $service->dummy($event)->shouldBeCalled()->willReturn(true);

        $this->call($event)->shouldReturn(true);
    }

    function it_converts_classname_to_class($container, TransitionEvent $event, ContainerAwareCallbackSpec $service)
    {
        $this->beConstructedWith(array(), array('Existing\\Service', 'dummy'), $container);

        $container->has('Existing\\Service')->willReturn(true);
        $container->get('Existing\\Service')->shouldBeCalled()->willReturn($service);

        $service->dummy($event)->shouldBeCalled()->willReturn(true);

        $this->call($event)->shouldReturn(true);
    }

    function it_allows_static_calls($container, TransitionEvent $event)
    {
        $this->beConstructedWith(array(), array('spec\\winzou\\Bundle\\StateMachineBundle\\Callback\\ContainerAwareCallbackSpec', 'dummyStatic'), $container);

        $container->has('spec\\winzou\\Bundle\\StateMachineBundle\\Callback\\ContainerAwareCallbackSpec')->willReturn(false);

        $this->call($event)->shouldReturn(2);
    }

    function it_throws_an_exception_when_relevant($container, TransitionEvent $event)
    {
        $this->beConstructedWith(array(), array('@my_service', 'dummy'), $container);

        $container->has('my_service')->willReturn(false);
        $container->get('my_service')->willThrow('Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException');

        $this->shouldThrow('Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException')->during('call', array($event));
    }

    function dummy()
    {
        return true;
    }

    static function dummyStatic()
    {
        return 2;
    }

    function it_does_not_convert_object($container, TransitionEvent $event, ContainerAwareCallbackSpec $service)
    {
        $this->beConstructedWith(array(), array($service, 'dummy'), $container);

        $service->dummy($event)->shouldBeCalled()->willReturn(true);

        $this->call($event)->shouldReturn(true);
    }
}
