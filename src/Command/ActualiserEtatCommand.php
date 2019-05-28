<?php

namespace App\Command;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Service\EtatManager;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ActualiserEtatCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'app:actualiser-etat';
    private $etatManager;

//    /**
//     * ActualiserEtatCommand constructor.
//     */
//    public function __construct(EtatManager $etatManager)
//    {
//        $this->etatManager = $etatManager;
//    }

    protected function configure()
    {
        $this
            ->setDescription('Actualise l\'état des sortie')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $io = new SymfonyStyle($input, $output);

        $output->writeln([
            '',
            'Actualisation de l\'état des sorties',
            '============',
        ]);
        $today = new \DateTime();
        $output->writeln('Nous sommes le : '.$today->format('d-m-Y'));

        $dateMoinsUnMois = $today->modify('-1 month');
        $output->writeln('Les sorties qui se sont déroulées avant le '.$dateMoinsUnMois->format('d-m-Y') .' seront archivées');

        //on récupère le repository de Sortie et Etat
        $doctrine = $this->getContainer()->get('doctrine');
        $em = $doctrine->getEntityManager();
        $sortiesRepo = $em->getRepository(Sortie::class);
        $etatRepo = $em->getRepository(Etat::class);

        //on récupères toutes les sorties
        $sorties = $sortiesRepo->findAll();
        $sortiesActualisees = array();

        //on archive les sorties passées depuis + d'un mois   $etatRepo->find(7) = "archivée"
        foreach ($sorties as $sortie){
            if($sortie->getDateHeureDebut() < $dateMoinsUnMois ){
                $sortie->setEtat($etatRepo->find(7));
                $em->persist($sortie);
                $em->flush();
                $sortiesActualisees[] = $sortie;
            }
        }

        //on affiche sur la console les sorties qui ont été mises à jour
        $output->writeln("============");
        if(sizeof($sortiesActualisees)!=0){
            $output->writeln('Sorties archivées :');
            foreach ($sortiesActualisees as $sortie){
                $output->writeln($sortie->getNom());
            }
        } else {
            $output->writeln('Aucune sortie à archiver');
        }

        $io->success('L\'état des sorties a bien été actualisé !');
    }
}
