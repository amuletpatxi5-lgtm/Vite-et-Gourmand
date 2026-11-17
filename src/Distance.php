<?php

declare(strict_types=1);

namespace App;

// Distance de livraison depuis le local de Vite & Gourmand (énoncé p. 7).
// 1. Géocodage de l'adresse : API Géoplateforme de l'IGN (sans clé)
// 2. Distance routière : OpenRouteService (clé ORS_API_KEY)
// 3. En secours : distance à vol d'oiseau multipliée par un coefficient de détour
final class Distance
{
    private const GEOCODAGE = 'https://data.geopf.fr/geocodage/search';
    private const ITINERAIRE = 'https://api.openrouteservice.org/v2/directions/driving-car';

    // Point de départ : le local à Bordeaux (longitude, latitude)
    private const DEPART_LON = -0.5735;
    private const DEPART_LAT = 44.8417;

    private const CODE_INSEE_BORDEAUX = '33063';
    private const SCORE_MINIMUM = 0.5;   // en dessous, l'adresse trouvée est trop incertaine
    private const DETOUR = 1.3;          // la route est en moyenne 30 % plus longue que la ligne droite

    /**
     * @return array{adresse: string, dans_bordeaux: bool, distance_km: float, methode: string}|null
     *         null si l'adresse est introuvable
     */
    public static function calculer(string $adresse, string $codePostal, string $ville): ?array
    {
        $point = self::geocoder("$adresse $codePostal $ville", $codePostal);
        if ($point === null) {
            return null;
        }

        // Livraison dans Bordeaux : gratuite, pas besoin de distance
        if ($point['citycode'] === self::CODE_INSEE_BORDEAUX) {
            return ['adresse' => $point['label'], 'dans_bordeaux' => true, 'distance_km' => 0.0, 'methode' => 'aucune'];
        }

        $km = self::distanceRoute($point['lon'], $point['lat']);
        $methode = 'route';
        if ($km === null) {
            $km = self::volOiseau($point['lon'], $point['lat']) * self::DETOUR;
            $methode = 'estimation';
        }

        return ['adresse' => $point['label'], 'dans_bordeaux' => false, 'distance_km' => round($km, 1), 'methode' => $methode];
    }

    private static function geocoder(string $recherche, string $codePostal): ?array
    {
        $url = self::GEOCODAGE . '?' . http_build_query([
            'q' => $recherche,
            'postcode' => $codePostal,
            'index' => 'address',
            'limit' => 1,
        ]);
        $reponse = self::getJson($url);
        $resultat = $reponse['features'][0] ?? null;

        if ($resultat === null || ($resultat['properties']['score'] ?? 0) < self::SCORE_MINIMUM) {
            return null;
        }

        [$lon, $lat] = $resultat['geometry']['coordinates'];
        return [
            'lon' => (float) $lon,
            'lat' => (float) $lat,
            'citycode' => (string) $resultat['properties']['citycode'],
            'label' => (string) $resultat['properties']['label'],
        ];
    }

    private static function distanceRoute(float $lon, float $lat): ?float
    {
        $cle = (string) getenv('ORS_API_KEY');
        if ($cle === '') {
            return null;
        }

        $url = self::ITINERAIRE . '?' . http_build_query([
            'start' => self::DEPART_LON . ',' . self::DEPART_LAT,
            'end' => "$lon,$lat",
        ]);
        $reponse = self::getJson($url, ["Authorization: $cle"]);
        $metres = $reponse['features'][0]['properties']['summary']['distance'] ?? null;

        return $metres === null ? null : $metres / 1000;
    }

    // Formule de haversine : distance sur la sphère terrestre, en km
    private static function volOiseau(float $lon, float $lat): float
    {
        $rayon = 6371;
        $dLat = deg2rad($lat - self::DEPART_LAT);
        $dLon = deg2rad($lon - self::DEPART_LON);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad(self::DEPART_LAT)) * cos(deg2rad($lat)) * sin($dLon / 2) ** 2;

        return 2 * $rayon * asin(sqrt($a));
    }

    // Appel HTTP GET qui renvoie le JSON décodé, ou null en cas d'échec (jamais d'exception)
    private static function getJson(string $url, array $entetes = []): ?array
    {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => 5,
            CURLOPT_HTTPHEADER => array_merge(['Accept: application/json, application/geo+json'], $entetes),
        ]);
        $corps = curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($corps === false || $code !== 200) {
            error_log("Distance : échec de l'appel $url (HTTP $code)");
            return null;
        }
        $json = json_decode((string) $corps, true);

        return is_array($json) ? $json : null;
    }
}