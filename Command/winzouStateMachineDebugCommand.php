<?php

namespace winzou\Bundle\StateMachineBundle\Command;

use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

class winzouStateMachineDebugCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        parent::configure();

        $this->setName('debug:winzou:state-machine');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $key = $input->getArgument('key');

        if (!array_key_exists($key, $this->config)) {
            throw new \RuntimeException("The provided state machine key is not configured.");
        }

        $config = $this->config[$key];

        $this->printStates($config['states'], $output);
        $this->printTransitions($config['transitions'], $output);
    }

    /**
     * @param $states
     * @param OutputInterface $output
     */
    protected function printStates($states, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(array('Configured States:'));

        foreach ($states as $state) {
            $table->addRow([$state]);
        }

        $table->render();
    }

    /**
     * @param $transitions
     * @param OutputInterface $output
     */
    protected function printTransitions($transitions, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(array('Transition', 'FROM', 'TO'));

        $previousTo = null;
        foreach ($transitions as $name => $transition) {
            foreach ($transition['from'] as $from) {

                if (!empty($previousTo && $previousTo != $transition['to'])) {
                    $table->addRow(new TableSeparator());
                }

                if ($previousTo == $transition['to']) {
                    $table->addRow(array('', $from, ''));
                } else {
                    $table->addRow(array($name, $from, $transition['to']));
                }

                $previousTo = $transition['to'];
            }
        }

        $table->render();
    }
}