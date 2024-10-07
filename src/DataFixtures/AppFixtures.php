<?php

namespace App\DataFixtures;

use App\Entity\Adresse;
use App\Entity\Client;
use App\Entity\Devis;
use App\Entity\Element;
use App\Entity\Entreprise;
use App\Entity\Prestation;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail("user@moto.com");
        $user->setUsername("user1");
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, "password"));
        $user->setRegisteredAt(new \DateTimeImmutable());
        $manager->persist($user);

        $user2 = new User();
        $user2->setEmail("user2@moto.com");
        $user2->setUsername("user2");
        $user2->setRoles(["ROLE_USER"]);
        $user2->setPassword($this->userPasswordHasher->hashPassword($user2, "password"));
        $user2->setRegisteredAt(new \DateTimeImmutable());
        $manager->persist($user2);

        $adresse1 = new Adresse();
        $adresse1->setNumero(3);
        $adresse1->setRue("rue de la mairie");
        $adresse1->setCp("75010");
        $adresse1->setVille("Paris");
        $adresse1->setPays("France");
        $adresse1->setUser($user);
        $manager->persist($adresse1);

        $adresse2 = new Adresse();
        $adresse2->setNumero(4);
        $adresse2->setRue("rue de la gare");
        $adresse2->setComplementaire("appt B203");
        $adresse2->setCp("34800");
        $adresse2->setVille("Peret");
        $adresse2->setPays("France");
        $adresse2->setUser($user);
        $manager->persist($adresse2);

        $adresse3 = new Adresse();
        $adresse3->setNumero(5);
        $adresse3->setRue("rue de la gare");
        $adresse3->setComplementaire("appt B203");
        $adresse3->setCp("34200");
        $adresse3->setVille("Bisous");
        $adresse3->setPays("France");
        $adresse3->setUser($user2);
        $manager->persist($adresse3);

        $client = new Client();
        $client->setEmail("client@moto.com");
        $client->setNom("LaFouine");
        $client->setPrenom("Gégé");
        $client->setAdresse($adresse1);
        $client->setTelephone("0612345678");
        $client->setUser($user);
        $manager->persist($client);

        $client2 = new Client();
        $client2->setEmail("client2@moto.com");
        $client2->setNom("Le tordu");
        $client2->setPrenom("Germaine");
        $client2->setAdresse($adresse2);
        $client2->setTelephone("0612995678");
        $client2->setUser($user2);
        $manager->persist($client2);

        $element = new Element();
        $element->setNom("Création d'un site web Wordpress ayant 6 pages");
        $element->setUser($user);
        $manager->persist($element);

        $element2 = new Element();
        $element2->setNom("Création d'un espace membre avec un forum");
        $element2->setUser($user);
        $manager->persist($element2);

        $entreprise = new Entreprise();
        $entreprise->setNom("Ma super entreprise");
        $entreprise->setAdresse($adresse3);
        $entreprise->setTelephone1("0612345678");
        $entreprise->setSiret("12345678901234");
        $entreprise->setWeb("https://www.ma-super-entreprise.fr");
        $entreprise->setEmail("entreprise@moto.com");
        $entreprise->setContact("Juju Martinus");
        $entreprise->setUser($user);
        $manager->persist($entreprise);

        $devis = new Devis();
        $devis->setUser($user);
        $devis->setClient($client);
        $devis->setEntreprise($entreprise);
        $devis->setReference("JMD-1234567890");
        $devis->setCreatedAt(new \DateTimeImmutable());
        $debut = new \DateTime();
        $debut->modify('+1 month');
        $devis->setDateValidite(new \DateTime($debut->format('Y-m-d')));
//        $devis->setDateDebutPrestation(new \DateTime($debut));
//        $devis->setDateDebutPrestation(new \DateTimeImmutable());
        $devis->setTc("Le client sera en charge de prendre un abonnement pour un hébergement en ligne (je conseille Hostinger) ainsi que l’achat d’un nom de
domaine et d’un certificat SSL si celui-ci n’est pas prévu dans le plan d’hébergement.
Un site « beta » sera mis à disposition du client pour validation du produit final.
La livraison finale du site (mise en ligne chez l’hébergeur choisi) ainsi que la mise à disposition du code source sera effectuée une fois le
paiement final reçu.
Au cas où le client choisirait un thème personnalisé, la maquette devra être validée avant le début de la phase de création du site. En cas
de modification ultérieure du design, les modifications du code qui découlent de cette mise à jour seront facturées 300 euros par heure.");

        $prestation = new Prestation();
        $prestation->setElement($element);
        $prestation->setQty(1);
        $prestation->setPrixHT(200000);
        $prestation->setTvaPercentage(20);
        $prestation->setTva(40000);
        $prestation->setTotalTTC(240000);
        $prestation->setTotalHT(200000);
        $prestation->setDevis($devis);
        $prestation->setUser($user);

        $prestation2 = new Prestation();
        $prestation2->setElement($element2);
        $prestation2->setQty(1);
        $prestation2->setPrixHT(50000);
        $prestation2->setTvaPercentage(20);
        $prestation2->setTva(10000);
        $prestation2->setTotalTTC(60000);
        $prestation2->setTotalHT(50000);
        $prestation2->setDevis($devis);
        $prestation2->setUser($user);

//        $prestations = $devis->getPrestations();
//        $totalTTC = 0;
//        foreach ($prestations as $prestation) {
//            $totalTTC += $prestation->getTotalTTC();
//        }
        $devis->setTotalHT(250000);
        $devis->setTva(50000);
        $devis->setTotalTTC(300000);

         $manager->persist($prestation);
         $manager->persist($prestation2);
         $manager->persist($devis);

        $manager->flush();
    }
}
