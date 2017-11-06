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

        $container->get('my_service')->shouldBeCalled()->willReturn($service);

        $service->dummy($event)->shouldBeCalled()->willReturn(true);

        $this->call($event)->shouldReturn(true);
    }

    function it_throws_an_exception_when_relevant($container, TransitionEvent $event, ContainerAwareCallbackSpec $service)
    {
        $this->beConstructedWith(array(), array('@my_service', 'dummy'), $container);

        $container->get('my_service')->shouldBeCalled()->willThrow('Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException');

        $this->shouldThrow('Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException')->during('call', array($event));
    }

    function dummy()
    {
        return true;
    }

    function it_does_not_convert_object($container, TransitionEvent $event, ContainerAwareCallbackSpec $service)
    {
        $this->beConstructedWith(array(), array($service, 'dummy'), $container);

        $service->dummy($event)->shouldBeCalled()->willReturn(true);

        $this->call($event)->shouldReturn(true);
    }
}
