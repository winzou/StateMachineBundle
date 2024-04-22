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
use Psr\Container\ContainerInterface;

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
     *
     * @return mixed
     */
    public function call(TransitionEvent $event)
    {
        // Load the services only now (when the callback is actually called)
        if (is_array($this->callable) && is_string($this->callable[0])) {
            if (0 === strpos($this->callable[0], '@')) {
                // BC for sf < 4.0, we refer to the service via its '@name'
                $serviceId = substr($this->callable[0], 1);
            } else {
                $serviceId = $this->callable[0];

                // We allow static calls so no Exception thrown if callable is not a service
                if (!$this->container->has($serviceId)) {
                    return parent::call($event);
                }
            }

            // Callback services have to be public
            $this->callable[0] = $this->container->get($serviceId);
        }

        return parent::call($event);
    }
}
