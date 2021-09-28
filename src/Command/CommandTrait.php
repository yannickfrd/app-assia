<?php

namespace App\Command;

use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Output\ConsoleOutput;

// [30m green bg
// [37m red bg
// [42m black fg
// [41m white fg
trait CommandTrait
{
    protected $output;

    /**
     * @param string|iterable $msg The message as an iterable of strings or a single string
     */
    protected function writeMessage(string $type, $msg)
    {
        $output = new ConsoleOutput();

        switch ($type) {
            case 'success':
                $outputStyle = new OutputFormatterStyle('black', 'green');
                $output->getFormatter()->setStyle($type, $outputStyle);
                $prefix = '[OK]';
                break;
            case 'error':
                $outputStyle = new OutputFormatterStyle('white', 'red');
                $output->getFormatter()->setStyle($type, $outputStyle);
                $prefix = '[Error]';
                break;
            default:
                $prefix = '[Info]';
                break;
        }

        return $output->writeln("<$type>\n $prefix $msg \n</>");
    }
}
