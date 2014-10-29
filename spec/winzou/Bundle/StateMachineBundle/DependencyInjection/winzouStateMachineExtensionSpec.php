<?php

namespace spec\winzou\Bundle\StateMachineBundle\DependencyInjection;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use winzou\Bundle\StateMachineBundle\DependencyInjection\Configuration;
use winzou\Bundle\StateMachineBundle\DependencyInjection\winzouStateMachineExtension;

class winzouStateMachineExtensionSpec extends ObjectBehavior
{
    private $configs = array(
        array(
            'graph1' => array(
                'class'  => 'Dummy',
                'states' => array(
                    'state1',
                    'index' => 'state2',
                    'state3' => null,
                    'state4',
                    'state4' => winzouStateMachineExtension::CFG_STATE_DISABLE
                ),
                'callbacks' => array(
                    'before' => array(
                        'callback1' => array(
                            'do' => 'dummy'
                        ),
                        'callback2' => array(
                            'do' => 'dummy',
                            'priority' => 5
                        ),
                        'callback3' => array(
                            'do' => 'dummy',
                            'priority' => -5
                        ),
                        'callback4' => array(
                            'do' => 'dummy'
                        ),
                        'callback4' => array(
                            'disabled' => true
                        )
                    ),
                    'after' => array(

                    )
                )
            ),
            'graph2' => array(
                'class'  => 'Dummy',
                'states' => array(
                    'state20'
                )
            )
        ),
        array(
            'graph1' => array(
                'callbacks' => array(
                    'before' => array(
                        'callback4' => array(
                            'disabled' => true
                        )
                    )
                )
            )
        )
    );

    private $parsedConfigs = array(
        'graph1' => array(
            'class'  => 'Dummy',
            'states' => array(
                'state1',
                'state2',
                'state3'
            ),
            'callbacks' => array(
                'before' => array(
                    'callback3' => array(
                        'do' => 'dummy',
                        'priority' => -5,
                    ),
                    'callback1' => array(
                        'do' => 'dummy'
                    ),
                    'callback2' => array(
                        'do' => 'dummy',
                        'priority' => 5
                    ),
                ),
                'after' => array(

                )
            )
        ),
        'graph2' => array(
            'class'  => 'Dummy',
            'states' => array(
                'state20'
            )
        )
    );

    function it_is_initializable()
    {
        $this->shouldHaveType('winzou\Bundle\StateMachineBundle\DependencyInjection\winzouStateMachineExtension');
    }

    function it_parse_configs()
    {
        $configuration = new Configuration();
        $processor = new Processor();

        $configs       = $processor->processConfiguration($configuration, $this->configs);
        $parsedConfigs = $processor->processConfiguration($configuration, array($this->parsedConfigs));

        $this->parseConfig($configs)->shouldReturn($parsedConfigs);
    }
}
