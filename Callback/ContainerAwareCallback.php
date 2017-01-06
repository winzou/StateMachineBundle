<?php

/*
 * This file is part of the StateMachine package.
 *
 * (c) Alexandre Bacco
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace winzou\Bundle\StateMachineBundle\Callback;

use SM\Callback\Callback;
use SM\Event\TransitionEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerAwareCallback extends Callback
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(array $specs, $callable, ContainerInterface $container)
    {
        parent::__construct($specs, $callable);

        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function call(TransitionEvent $event)
    {
        // Load the services only now (when the callback is actually called)
        if (
            is_array($this->callable)
            && is_string($this->callable[0])
            && 0 === strpos($this->callable[0], '@')
            && $this->container->has($serviceId = substr($this->callable[0], 1))
        ) {
            $this->callable[0] = $this->container->get($serviceId);
        } elseif (
            is_array($this->callable)
            && is_string($this->callable[0])
            && 0 === strpos($this->callable[0], 'object')
        ) {
            $this->callable[0] = $event->getStateMachine()->getObject();
        }

        return parent::call($event);
    }
}
