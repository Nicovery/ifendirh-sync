<?php

namespace Ifendirh\SyncBundle\Command;

use Ifendirh\SyncBundle\Exec\DatabaseExporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ExportDatabaseCommand extends Command {

    protected function configure() {
        $this
                ->setName('export:databases')
                ->setDescription('Exporter toutes les bases de données')
                ->addArgument(
                        'databases', InputArgument::OPTIONAL, 'Exporter seulement les bases de données sélectionnées'
                )
                ->addOption('user', '-u', InputOption::VALUE_OPTIONAL, 'L\'utilisateur de la base de données', 'root')
                ->addOption('password', '-p', InputOption::VALUE_OPTIONAL, 'Le mot de passe pour accèder à la base de données')
                ->addOption('host', '-ht', InputOption::VALUE_OPTIONAL, 'Le serveur hébergeant la base de données','localhost')

        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
       
        $databases = $input->getArgument('databases');
        
        $user = $input->getOption('user');
        $password = $input->getOption('password');
        $host = $input->getOption('host');
        
        if(!empty($password)){
            $this->exportDatabases($user,$password,$host,$databases,$output);
        }else{
            $this->stepByStepActions($databases,$output);
        }
    }

    protected function stepByStepActions($databases,$output) {
        $dialog = $this->getHelperSet()->get('dialog');
        $user = $dialog->ask(
                $output,
                '<info>L\'utilisateur de la base de données ? (root)</info>',
                'root'
                );
        $password = $dialog->ask(
                $output,
                '<info>Le mot de passe pour accèder à la base de données : </info>'
                );
        $host = $dialog->ask(
                $output,
                '<info>Le serveur hébergeant la base de données (localhost): </info>',
                'localhost'
                );
        $this->exportDatabases($user,$password,$host,$databases,$output);
    }

    protected function exportDatabases($user,$password,$host,$databases,$output) {
        
        $dialog = $this->getHelperSet()->get('dialog');
        $msgConfirm = "<question>Voulez-vous exporter toutes les bases de données ?(y/n)</question>";
        if (!empty($databases)) {
            $msgConfirm = "<question>Voulez-vous exporter les bases de données sélectionnées : " . $databases.'?(y/n)</question>';
        }
        if ($dialog->askConfirmation(
                        $output, $msgConfirm, false
                )) {
            $output->writeln('<info>Exportation lancée...</info>');

            $databaseExporter = new DatabaseExporter($output);
            $databaseExporter->export($user,$password,$host,$databases);

            $output->writeln('<info>Exportation terminée</info>');
        } else {
            $output->writeln('<error>Exportation abandonnée</error>');
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
        $helpMessage = "<comment>Si aucun mot de passe n'est spécifié par l'option --password ou -p alors le mode intéractif sera automatiquement lancé.</comment>\n\n";
        $helpMessage .= "Exporter toutes les bases de données \n<info>php sync export:databases --user=root --password=123456</info>\n";
        $helpMessage .= "Exporter une base de données \n<info>php sync export:databases ma_base --user=root --password=123456</info>\n";
        return parent::getHelp().$helpMessage;
    }

}
