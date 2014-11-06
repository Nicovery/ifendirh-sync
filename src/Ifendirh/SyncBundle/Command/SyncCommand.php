<?php

namespace Ifendirh\SyncBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ifendirh\SyncBundle\Message\SyncMessage;
use Ifendirh\SyncBundle\Exec\Synchronizer;
use Ifendirh\SyncBundle\Utils\ExternDriveTools;

class SyncCommand extends Command {

    protected function configure() {
        $this
                ->setName('var')
                ->setDescription(SyncMessage::DIRECTORY_TO_SYNC)
                ->addArgument(
                        'to_directory', InputArgument::OPTIONAL, 'Le répertoire'
                )
                ->addOption('reverse', '-r', InputOption::VALUE_NONE, 'Si défini. Synchronise le répertoire distant vers le répertoire /var/www local')
                ->addOption('var_directory', '-vd', InputOption::VALUE_OPTIONAL, 'Le répertoire à partir duquel la synchronisation est lancé.')
                ->addOption('directory', '-d', InputOption::VALUE_OPTIONAL, 'Le répertoire à partir duquel la synchronisation est lancé depuis /var/www.')
                

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $dialog = $this->getHelperSet()->get('dialog');
        $toDirectory = $input->getArgument('to_directory');
        $customVarDirectory = $input->getOption('var_directory');
        $specificDirectory = $input->getOption('directory');
        $isReverse = $input->getOption('reverse');
        
        if(empty($toDirectory)){
            $externalDriveTools = new ExternDriveTools();
            $toDirectory = $externalDriveTools->getWWWDirectory();
        }
        
        $fromDirectory = '/var/www';
        if (!empty($customVarDirectory)) {
            $fromDirectory = $customVarDirectory;
        }
        
        if(!empty($specificDirectory)){
            //add "/"
            if (substr($fromDirectory, -1) != '/') {
                $fromDirectory .= '/';
            }
            $fromDirectory .= $specificDirectory;
        }

        if ($isReverse) {
            $tmpRenaming = $fromDirectory;
            $fromDirectory = $toDirectory;
            $toDirectory = $tmpRenaming;
        }

        if ($dialog->askConfirmation(
                        $output, '<question>Synchroniser [' . $fromDirectory . '] > [' . $toDirectory . '] ? y/n</question>', false
                )) {
            $output->writeln('<info>Synchronisation lancée...</info>');
            
            $synchronizer = new Synchronizer($output);
            $synchronizer->rsync($fromDirectory, $toDirectory);
         
            $output->writeln('<info>Synchronisation terminée</info>');
        } else {
            $output->writeln('<error>Synchronisation abandonnée</error>');
        }
    }
    
    /**
     * Returns the help for the command.
     *
     * @return string The help for the command
     *
     * @api
     */
    public function getHelp()
    {
        $helpMessage = "Synchroniser tout le répertoire /var/www vers le disque externe \n<info>php sync var</info>\n";
        $helpMessage .= "Synchroniser le disque externe vers le répertoire /var/www \n<info>php sync var --reverse</info>\n";
        $helpMessage .= "Synchroniser un dossier en particulier du répertoire /var/www  vers le disque externe \n<info>php sync var --directory=my_directory</info>\n";
        return parent::getHelp().$helpMessage;
    }
    
    

}
