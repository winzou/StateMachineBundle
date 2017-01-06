<?php

namespace spec\winzou\Bundle\StateMachineBundle\Callback;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SM\Event\TransitionEvent;
use SM\StateMachine\StateMachine;
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

    function it_use_object_class_to_do_the_callback(
        $container,
        TransitionEvent $event,
        StateMachine $sm
    ) {
        $object = new Dummy();
        $this->beConstructedWith(array(), array('object', 'methodToBeCalled'), $container);

        $event->getStateMachine()->willReturn($sm);
        $sm->getObject()->willReturn($object);

        $this->call($event)->shouldReturn(true);
    }
}

class Dummy
{
    public function methodToBeCalled()
    {
        return true;
    }
}
