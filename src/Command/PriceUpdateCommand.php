<?php

namespace App\Command;

use App\Entity\Calculation\Price;
use App\Repository\Calculation\PriceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'price:update',
    description: 'Permet de mettre à jour les prix',
)]
class PriceUpdateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly PriceRepository        $priceRepository,
        string                                  $name = null
    )
    {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('file', InputArgument::OPTIONAL, 'Fichier à injecter')
            ->addArgument('location', InputArgument::OPTIONAL, 'Pour sol (S) ou toit (T) ?')
            ->addArgument('type', InputArgument::OPTIONAL, 'Industriel (I) ou Commercial (C) ?');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $file = $input->getArgument('file');
        $location = $input->getArgument('location');
        $type = $input->getArgument('type');

        if ($location && $location !== 'S' && $location !== 'T') {
            $io->error("$location n'est pas une location valide : S pour Sol ou T pour Toit attendu");

            return Command::FAILURE;
        }

        if ($type && $type !== 'I' && $type !== 'C') {
            $io->error("$type n'est pas une type valide : I pour Industriel ou C pour Commercial attendu");

            return Command::FAILURE;
        }

        $isEligibleToBonus = !($location === 'S');

        $nbRow = 0;

        $fileFullPath = static::getAssetsDirectory() . $file;

        if (!file_exists($fileFullPath)) {
            $io->error("le fichier $file n'a pas été trouvé dans le répertoire " . static::getAssetsDirectory());

            return Command::FAILURE;
        }

        $this->priceRepository->deleteAllPricesWithPlaceAndType($location, $type);

        if (($handle = fopen($fileFullPath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

                $count = count(explode(';', $data[0]));
                if ($count === 4) {
                    list($nb, $priceA, $priceP1, $priceP2) = explode(';', $data[0]);
                    $priceP3 = null;
                    $priceP4 = null;
                } else {
                    list($nb, $priceA, $priceP1, $priceP2, $priceP3, $priceP4) = explode(';', $data[0]);
                }

                if (is_numeric($nb)) {
                    $priceRow = new Price();
                    $priceRow
                        ->setNbPanels($nb)
                        ->setPriceBasic($priceA)
                        ->setPriceDiscounted1($priceP1)
                        ->setPriceDiscounted2($priceP2)
                        ->setPriceDiscounted3($priceP3)
                        ->setPriceDiscounted4($priceP4)
                        ->setInstallationType($type)
                        ->setPlace($location)
                        ->setIsEligibleToBonus($isEligibleToBonus);
                    $nbRow++;
                    $this->manager->persist($priceRow);
                }
            }
            fclose($handle);
        }
        $this->manager->flush();

        $io->success("$nbRow prix ont bien été ajoutés à la base de données");

        return Command::SUCCESS;
    }

    private function getAssetsDirectory(): string
    {
        $kernelContainer = $this->getApplication()->getKernel()->getContainer();
        if ($kernelContainer->hasParameter('kernel.project_dir')) {
            return $kernelContainer->getParameter('kernel.project_dir') . '/' . 'assets/files/';
        }
        return '';
    }
}
