<?php

namespace App\DataFixtures;

use App\Entity\Personnel;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PersonnelFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $grades = [
            'NON RENSEIGNE','Elève Gendarme','Gendarme Stagiaire','Gendarme de Deuxième Classe','Médecin Colonel','Médecin Général',
            'Gendarme de Première Classe','Gendarme Hors Classe','Gendarme Principal de Deuxième Classe','Médecin Lieutenant-Colonel',
            'Gendarme Principal de Première Classe','Gendarme Principal Hors Classe','Gendarme Principal de Classe Exceptionnelle',
            'Sous-Lieutenant','Lieutenant','Capitaine','Chef d\'Escadron','Lieutenant-Colonel','Colonel','General de Brigade',
            'General de Division','General de Corps d\'Arme','General d\'Arme','EMPLOYE DE SERVICE','ASSISTANT DE SERVICE',
            'ADJOINTS DE SERVICE','TECHNICIEN SUPERIEUR CHARGE D\'ENSEIGNEMENT','ATTACHE DE SERVICE','EMPLOYE D\'ADMINISTRATION',
            'OPERATEUR OU ASSISTANT D\'ADMINISTRATION','INSTITUTEUR','ADJOINTS OU ENCADREURS','A3','ATTACHES D\'ADMINISTRATION OU REALISATEUR ADJOINT',
            'MEDECIN DE L\'AM','PROF LICENCIE','ADMINISTRATEUR','MED DE OU MAGISTRAT','OUVRIER SPECIALISE DE LA PREMIERE CATEGORIE','OUVRIER SPECIALISE DE LA DEUXIEME CATEGORIE',
            'OUVRIER PROFESSIONNEL DE LA  CATEGORIE 1A','OUVRIER PROFESSIONNEL DE LA CATEGORIE 1B','Médecin Lieutenant','Médecin Capitaine','Médecin Commandant',
        ];

        $firstNames = [
            'Rasoa', 'Rabe', 'Ravo', 'Andry', 'Hery', 'Lala', 'Miora', 'Tiana', 'Nono', 'Faly',
            'Jean', 'Pierre', 'Marie', 'Luc', 'Sophie', 'Paul', 'Julie', 'Michel', 'Nina', 'David',
            'Alice', 'Bob', 'Chloe', 'David', 'Eva', 'Frank', 'Grace', 'Hannah', 'Ian', 'Jack'
        ];
        $lastNames = [
            'RAKOTONDRABE', 'ANDRIANARIVO', 'RANDRIANASOLO', 'RAKOTOARIMANANA', 'RABEMANANJARA',
            'ANDRIAMANJATO', 'RAKOTOMALALA', 'RANDRIAMAMPIONONA', 'RABEARIMANANA', 'ANDRIANJAKA', 
            'RAKOTOVAO', 'RANDRIATSARAFARA', 'RABENIRINA', 'ANDRIANASOLO', 'RAKOTOBE',
            'RANDRIANARIMANANA', 'RABEMANJAKA', 'ANDRIANJATO', 'RAKOTOMANGA', 'RANDRIAMPIANINA'
        ];

        $LIBUTES = [
            'EMZP','COM/GN','COM/CAB','COM/CAB/COUR.','COM/ESCADRON IVROGNES','COM/CAB/IT','COM/CAB/PSR',
            'COM/CAB/PCOU','COM/CAB/SEGEP','COM/CAB/SRI','COM/SM','COM/DQG','COM/DQG/INFR','COM/DQG/SAG',
            'COM/DQG/SPORT','COM/DQG/ARMS','COM/DQG/SRH','COM/DQG/SAF','COM/DQG/SM','COM/DQG/ST',
            'COM/DQG/ST/INFR','COM/DQG/ST/ARMS','COM/SJD','COM/DSR','COM/DSR/SLCT','COM/DSR/INFO',
            'COM/DSR/PJ','COM/DSR/CCOG','COM/DSR/PJ/PT','COM/DSR/PJ/FIC','COM/DSR/PJ/BS','COM/DSR/SLCVB',
            'COM/DSR/SCRP','COM/DSR/OPS','COM/DSR/RENS','COM/DSR/STUP','COM/DSR/SLOG','COM/DSR/ANACRIM',
            'COM/DSR/SAF','COM/DSR/BAK','COM/DOE','COM/DOE/SOS','COM/DOE/SE','COM/DOE/SJD','COM/DOE/SPD',
            'COM/DOE/SCSE','COM/DOE/SAC','COM/DOE/SAF','COM/DGP','COM/DGP/INFO','COM/DGP/SAF',
            'COM/DGP/SCONT','COM/DGP/SPSO','COM/DGP/SPSO/EFF','COM/DGP/SPSO/GESTION','COM/DGP/SPSO/DISC',
            'COM/DGP/SPSO/PENSION','COM/DGP/SPCA','COM/DGP/E.FORM',
            'COM/DGP/E.FORM DIV STAGE INTERIEUR','COM/DGP/E.FORM DIV STAGE EXTERIEUR',
            'COM/DGP/SPO','COM/DGP/SAS','COM/DGP/BGMUT','COM/DGP/OGN','COM/DGP/OGN/SAF',
            'COM/DLI','SEG/DAF/SFB','COM/DLI/CM','COM/DLI/SINT','COM/DLI/SAG','COM/DLI/SC',
            'COM/DLI/EMG','COM/DLI/EMG/IMPRIMERIE','COM/DLI/EMG/COUTURE','COM/DLI/SS',
            'COM/DLI/SD/CENTRAL','COM/DLI/SD/DEPENSES','COM/DLI/SD/SOLDE','COM/DLI/SD/DEPLACEMENT',
            'COM/DLI/SD/OPPOSITION','COM/DLI/PIP','COM/DLI/INFR','COM/DLI/ATELIER BOIS','COM/DLI/UGPM',
            'COM/DLI/ORG','COM/DLI/USE','COM/DLI/SIC','SEG/DAF/SIP','COM/DLI/SPA','COM/DLI/SAF',
            'COM/DLI/BGMUT','COM/DT','COM/DT/G. FINANCIERE','COM/DT/COMPTA.MAT','COM/DT/CONTR. ET INSP',
            'COM/DT/TELECOM','COM/DT/MATR','COM/DT/MATR/5° ECH','COM/DT/ARMS','COM/DT/ARMES/ETUDES',
            'COM/DT/ARMS/R2','COM/DT/ARMS/A3','COM/DT/ARMS/C','COM/DT/ARMS/A2','COM/DT/INFRA',
            'COM/DT/INFRA/DGI-DOM','COM/DT/INFRA/DT/DTE','COM/DT/INFRA/AT.BOIS','COM/DT/INFRA/AT.FER',
            'COM/DT/INFRA/APPRO-MARCHE','COM/DT/INFRA/ETUDES','COM/DT/INFRA/COMPTABILITE',
            'COM/DT/INFR/DTE','COM/DT/INFR/DEMA','COM/DT/INFR/ATB','COM/DT/SPIF','COM/DT/SAF',
            'COM/DGC','COM/DCI/SG','COM/DCI/AT','COM/DPJ','COM/DPJ/BS','COM/DPJ/SACS',
            'COM/DPJ/SACS/DAS','COM/DPJ/SACS/DAC','COM/DPJ/SCAJ','COM/DPJ/SCAJ/DS',
            'COM/DPJ/SFRJ','COM/DPJ/SFRJ/DRJ','COM/DPJ/SFRJ/DPS','COM/DPJ/SFRJ/DOMU',
            'COM/DPJ/SFRJ/DET','COM/DPJ/SPEM','COM/DPJ/SCDF','COM/DPJ/SLCC','COM/DPJ/SLDEF',
            'COM/DPJ/SAF','COM/DSIT','COM/DSIT/SEDI','COM/DSIT/SEMF','COM/DSIT/STELECOM',
            'COM/DSIT/SAF','COM/DSIT/SCRP','CIRGN','CIRGN/CAB','CIRGN/CAB/COURRIERS','CIRGN/BPD',
            'CIRGN/SA','CIRGN/SA/MAT','CIRGN/SE','CIRGN/SE/DAC','CIRGN/OPS','CIRGN/PDS',
            'CIRGN/RENS','CIRGN/SRH','CIRGN/SRH/DISC','CIRGN/SRH/GESTION','CIRGN/ST',
            'CIRGN/ST/TELECOM','CIRGN/ST/INFRA','CIRGN/ST/INFRA/ATB','CIRGN/ST/MATR',
            'CIRGN/ST/ARMS','CIRGN/ST/PIF','CCSR','CIRGN/INS','CIRGN/AS','SRC'
        ];

        $LOCALS = [
            'ANTANANARIVO', 'TOAMASINA', 'FARAFANGANA', 'MAHAJANGA', 'ANTSIRABE', 'FIANARANTSOA',
            'TOLAGNARO', 'MIANDRIVAZO', 'AMBATONDRAZAKA', 'MORAMANGA', 'SAMBAVA', 'NOSY BE',
            'BETROKA', 'IBELOKA', 'TSIHOMBE', 'AMBOVOMBE', 'ANALALAVA', 'ANDILAMENA', 'BESALAMPY',
            'SOALALA', 'ANKAZOABE', 'ANKARAFANTSIKA', 'MAROVOAY', 'MAHAJANGA II', 'AMBALAVAO', 'VOHIBINANY'
        ];

        for ($i = 1; $i <= 200; $i++) {
            $personnel = new Personnel();

            $mat = sprintf('%05d', $i);
            $personnel->setMatricule((string)$mat);

            $grade = $grades[array_rand($grades)];
            $nom = $firstNames[array_rand($firstNames)] . ' ' . $lastNames[array_rand($lastNames)];
            $libute = $LIBUTES[array_rand($LIBUTES)];
            $local = $LOCALS[array_rand($LOCALS)];
            $unite = $libute . ' ' . $local;

            $personnel->setGrade($grade);
            $personnel->setNom($nom);
            $personnel->setLIBUTE($unite);

            $manager->persist($personnel);
        }

        $manager->flush();
    }
}
