<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

#[AsCommand(
    name: 'chmod:cache',
    description: 'Permet de fixer les droits des dossiers `tmp` et `var` pour la génération des PDF avec mPDF',
)]
class ChmodFixCommand extends Command
{
    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $process = new Process(['chmod', '-R', '775', 'var/cache', 'var/log']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();

        $io = new SymfonyStyle($input, $output);
        $io->success("Les droits des dossiers de cache ont bien été mis à jour");

        return Command::SUCCESS;
    }
}