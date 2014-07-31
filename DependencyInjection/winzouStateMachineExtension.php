<?php

/*
 * This file is part of the StateMachine package.
 *
 * (c) Alexandre Bacco
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace winzou\Bundle\StateMachineBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class winzouStateMachineExtension extends Extension
{
    const CFG_STATE_DISABLE = '::disabled';

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter('sm.configs', $this->parseConfig($config));

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');
    }

    /**
     * Does whatever is needed to transform the config in an acceptable argument for the factory
     *
     * @param array $configs
     *
     * @return array
     */
    public function parseConfig(array $configs)
    {
        foreach ($configs as &$config) {
            $config['states'] = $this->parseStates($config['states']);

            if (isset($config['callbacks'])) {
                $config['callbacks'] = $this->parseCallbacks($config['callbacks']);
            }
        }

        return $configs;
    }

    /**
     * Allows the disabling of states
     *
     * @param array $states
     *
     * @return array
     */
    protected function parseStates(array $states)
    {
        $newStates = array();
        foreach ($states as $key => $state) {
            if (null === $state) {
                $newStates[] = $key;
            } elseif (self::CFG_STATE_DISABLE !== $state) {
                $newStates[] = $state;
            } elseif (null !== $index = array_search($key, $newStates)) {
                unset($newStates[$index]);
            }
        }

        return $newStates;
    }

    /**
     * Allows the disabling of callbacks
     *
     * @param array $callbacks
     *
     * @return array
     */
    protected function parseCallbacks(array $callbacks)
    {
        foreach (array('before', 'after') as $position) {
            // Remove disabled callbacks
            foreach ($callbacks[$position] as $i => $callback) {
                if ($callback['disabled']) {
                    unset($callbacks[$position][$i]);
                }
            }

            // Order callbacks according to priority index
            uasort($callbacks[$position], function($a, $b) {
                if ($a['priority'] === $b['priority']) {
                    return 0;
                }
                return $a['priority'] < $b['priority'] ? -1 : 1;
            });
        }

        return $callbacks;
    }
}
