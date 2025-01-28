<?php

namespace App\Service;

use App\Entity\Adresse;

class AdresseFormatter
{
    public static function stringAdresse(Adresse $adresse): string
    {
        $complementaire = $adresse->getComplementaire() ?? null;
        $cp = $adresse->getCp() ?? null;
        $numero = $adresse->getNumero() ?? null;
        $pays = $adresse->getPays() ?? null;
        $rue = $adresse->getRue() ?? null;
        $ville = $adresse->getVille() ?? null;

        $adresseParts = [];

        if ($numero) {
            $adresseParts[] = $numero . ',';
        }
        if ($rue) {
            $adresseParts[] = $rue . ',';
        }
        if ($complementaire) {
            $adresseParts[] = $complementaire . ',';
        }
        if ($cp) {
            $adresseParts[] = $cp . ',';
        }
        if ($ville) {
            $adresseParts[] = $ville . ',';
        }
        if ($pays) {
            $adresseParts[] = $pays;
        }

        return implode(' ', $adresseParts);
    }

    public static function stringAdresseRue(Adresse $adresse): string
    {
        $rue = $adresse->getRue() ?? null;
        $complementaire = $adresse->getComplementaire() ?? null;
        $numero = $adresse->getNumero() ?? null;
        $adresseParts = [];

        if ($numero) {
            $adresseParts[] = $numero . ',';
        }
        if ($rue) {
            $adresseParts[] = $rue . ',';
        }
        if ($complementaire) {
            $adresseParts[] = $complementaire . ',';
        }

        return implode(' ', $adresseParts);
    }

    public static function stringAdresseVille(Adresse $adresse): string
    {
        $cp = $adresse->getCp() ?? null;
        $ville = $adresse->getVille() ?? null;
        $pays = $adresse->getPays() ?? null;
        $adresseParts = [];

        if ($cp) {
            $adresseParts[] = $cp . ',';
        }
        if ($ville) {
            $adresseParts[] = $ville . ',';
        }
        if ($pays) {
            $adresseParts[] = $pays;
        }

        return implode(' ', $adresseParts);
    }
}
