<?php

namespace App\Helpers;

// (Este es un convertidor de números a letras simple. Puedes reemplazarlo por uno más robusto)
class NumberToWords
{
    private static $unidades = ['', 'UNO', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
    private static $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
    private static $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];
    private static $excepciones = [
        11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE', 15 => 'QUINCE',
        16 => 'DIECISEIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO', 19 => 'DIECINUEVE',
        21 => 'VEINTIUNO', 22 => 'VEINTIDOS', 23 => 'VEINTITRES', 24 => 'VEINTICUATRO', 25 => 'VEINTICINCO',
        26 => 'VEINTISEIS', 27 => 'VEINTISIETE', 28 => 'VEINTIOCHO', 29 => 'VEINTINUEVE',
        100 => 'CIEN'
    ];

    public static function convert($number, $moneda = 'SOLES')
    {
        $partes = explode('.', number_format($number, 2, '.', ''));
        $entero = (int) $partes[0];
        $decimal = (int) $partes[1];

        $textoEntero = self::convertirEntero($entero);
        
        $textoMoneda = "Y {$decimal}/100 {$moneda}";

        return trim("{$textoEntero} {$textoMoneda}");
    }

    private static function convertirEntero($n)
    {
        if ($n < 10) {
            return self::$unidades[$n];
        }
        if ($n < 100) {
            return self::convertirDecenas($n);
        }
        if ($n < 1000) {
            return self::convertirCentenas($n);
        }
        if ($n < 1000000) {
            return self::convertirMiles($n);
        }
        if ($n < 1000000000) {
            return self::convertirMillones($n);
        }
        return 'NÚMERO DEMASIADO GRANDE';
    }

    private static function convertirDecenas($n)
    {
        if (isset(self::$excepciones[$n])) {
            return self::$excepciones[$n];
        }
        if ($n < 30) {
            return 'VEINTI' . self::$unidades[$n - 20];
        }
        $decena = self::$decenas[floor($n / 10)];
        $unidad = $n % 10;
        if ($unidad > 0) {
            return "{$decena} Y " . self::$unidades[$unidad];
        }
        return $decena;
    }

    private static function convertirCentenas($n)
    {
        if (isset(self::$excepciones[$n])) {
            return self::$excepciones[$n];
        }
        $centena = self::$centenas[floor($n / 100)];
        $resto = $n % 100;
        if ($resto > 0) {
            return "{$centena} " . self::convertirDecenas($resto);
        }
        return $centena;
    }

    private static function convertirMiles($n)
    {
        $mil = floor($n / 1000);
        $resto = $n % 1000;
        $textoMil = ($mil == 1) ? 'MIL' : self::convertirEntero($mil) . ' MIL';
        if ($resto > 0) {
            return "{$textoMil} " . self::convertirEntero($resto);
        }
        return $textoMil;
    }

    private static function convertirMillones($n)
    {
        $millon = floor($n / 1000000);
        $resto = $n % 1000000;
        $textoMillon = ($millon == 1) ? 'UN MILLON' : self::convertirEntero($millon) . ' MILLONES';
        if ($resto > 0) {
            return "{$textoMillon} " . self::convertirEntero($resto);
        }
        return $textoMillon;
    }
}