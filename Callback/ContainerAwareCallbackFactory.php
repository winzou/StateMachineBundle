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

use SM\SMException;
use SM\Callback\CallbackFactory;
use SM\Callback\CallbackInterface;
use Psr\Container\ContainerInterface;

class ContainerAwareCallbackFactory extends CallbackFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct($class, ContainerInterface $container)
    {
        parent::__construct($class);

        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function get(array $specs): CallbackInterface
    {
        if (!isset($specs['do'])) {
            throw new SMException(sprintf(
               'CallbackFactory::get needs the index "do" to be able to build a callback, array %s given.',
                json_encode($specs)
            ));
        }

        $class = $this->class;
        return new $class($specs, $specs['do'], $this->container);
    }
}
