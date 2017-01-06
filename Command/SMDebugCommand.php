<?php

namespace winzou\Bundle\StateMachineBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SMDebugCommand extends AbstractSMCommand
{
    public function configure()
    {
        parent::configure();
        $this->setName('debug:sm');
    }

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

    protected function printStates($config, OutputInterface $output)
    {
        $output->writeln('');
        $tableHelper = $this->getHelper('table');
        $tableHelper->setHeaders(['Configured States:']);
        foreach ($config as $state) {
            $tableHelper->addRow([$state]);
        }
        $tableHelper->render($output);
    }

    protected function printTransitions($config, OutputInterface $output)
    {
        $output->writeln("\nTransitions configured:");
        $tableHelper = $this->getHelper('table');
        $tableHelper->setRows([]);
        $tableHelper->setHeaders(['FROM', 'TO']);
        foreach ($config as $transition) {
            foreach ($transition['from'] as $from) {
                $tableHelper->addRow([$from, $transition['to']]);
            }
        }
        $tableHelper->render($output);
    }
}
