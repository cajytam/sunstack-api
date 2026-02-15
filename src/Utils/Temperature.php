<?php

namespace App\Utils;

class Temperature
{
    static public function getMinimalTemperatureByDepartment(string $departement): array
    {
        $minimalTemperatureByDepartment = [
            '2A' => [
                'temperature' => -3,
                'year' => 2015,
            ],
            '2B' => [
                'temperature' => -3,
                'year' => 2015,
            ],
            '29' => [
                'temperature' => -7,
                'year' => 2018,
            ],
            '22' => [
                'temperature' => -8,
                'year' => 2017,
            ],
            '35' => [
                'temperature' => -8,
                'year' => 2017,
            ],
            '44' => [
                'temperature' => -14,
                'year' => 2018,
            ],
            '49' => [
                'temperature' => -14,
                'year' => 2018,
            ],
            '56' => [
                'temperature' => -8,
                'year' => 2017,
            ],
            '85' => [
                'temperature' => -14,
                'year' => 2018,
            ],
            '79' => [
                'temperature' => -14,
                'year' => 2018,
            ],
            '75' => [
                'temperature' => -7,
                'year' => 2018,
            ],
            '83' => [
                'temperature' => -7,
                'year' => 2017,
            ],
            '50' => [
                'temperature' => -9,
                'year' => 2017,
            ],
            '11' => [
                'temperature' => -7,
                'year' => 2017,
            ],
            '09' => [
                'temperature' => -9,
                'year' => 2017,
            ],
            '40' => [
                'temperature' => -10,
                'year' => 2017,
            ],
            '66' => [
                'temperature' => -9,
                'year' => 2017,
            ],
            '13' => [
                'temperature' => -8,
                'year' => 2017,
            ],
            '84' => [
                'temperature' => -8,
                'year' => 2017,
            ],
            '32' => [
                'temperature' => -10,
                'year' => 2017,
            ],
            '31' => [
                'temperature' => -9,
                'year' => 2017,
            ],
            '65' => [
                'temperature' => -9,
                'year' => 2017,
            ],
            '82' => [
                'temperature' => -9,
                'year' => 2017,
            ],
            '64' => [
                'temperature' => -13,
                'year' => 2018,
            ],
            '58' => [
                'temperature' => -13,
                'year' => 2018,
            ],
            '16' => [
                'temperature' => -10,
                'year' => 2017,
            ],
            '86' => [
                'temperature' => -10,
                'year' => 2018,
            ],
            '94' => [
                'temperature' => -9,
                'year' => 2018,
            ],
            '53' => [
                'temperature' => -10,
                'year' => 2017,
            ],
            '72' => [
                'temperature' => -11,
                'year' => 2018,
            ],
            '23' => [
                'temperature' => -13,
                'year' => 2018,
            ],
            '87' => [
                'temperature' => -13,
                'year' => 2018,
            ],
            '69' => [
                'temperature' => -14,
                'year' => 2018,
            ],
            '92' => [
                'temperature' => -9,
                'year' => 2013,
            ],
            '93' => [
                'temperature' => -9,
                'year' => 2013,
            ],
            '91' => [
                'temperature' => -13,
                'year' => 2018,
            ],
            '45' => [
                'temperature' => -14,
                'year' => 2018,
            ],
            '77' => [
                'temperature' => -13,
                'year' => 2018,
            ],
            '90' => [
                'temperature' => -12,
                'year' => 2018,
            ],
            '89' => [
                'temperature' => -12,
                'year' => 2021,
            ],
            '10' => [
                'temperature' => -12,
                'year' => 2021,
            ],
            '21' => [
                'temperature' => -12,
                'year' => 2021,
            ],
            '52' => [
                'temperature' => -11,
                'year' => 2014,
            ],
            '18' => [
                'temperature' => -14,
                'year' => 2018,
            ],
            '36' => [
                'temperature' => -14,
                'year' => 2018,
            ],
            '37' => [
                'temperature' => -11,
                'year' => 2017,
            ],
            '41' => [
                'temperature' => -14,
                'year' => 2018,
            ],
            '01' => [
                'temperature' => -12,
                'year' => 2017,
            ],
            '39' => [
                'temperature' => -12,
                'year' => 2017,
            ],
            '03' => [
                'temperature' => -14,
                'year' => 2018,
            ],
            '42' => [
                'temperature' => -14,
                'year' => 2018,
            ],
            '63' => [
                'temperature' => -14,
                'year' => 2018,
            ],
            '71' => [
                'temperature' => -13,
                'year' => 2018,
            ],
            '81' => [
                'temperature' => -12,
                'year' => 2018,
            ],
            '14' => [
                'temperature' => -11,
                'year' => 2013,
            ],
            '28' => [
                'temperature' => -13,
                'year' => 2018,
            ],
            '61' => [
                'temperature' => -11,
                'year' => 2013,
            ],
            '78' => [
                'temperature' => -11,
                'year' => 2013,
            ],
            '04' => [
                'temperature' => -12,
                'year' => 2023,
            ],
            '06' => [
                'temperature' => -12,
                'year' => 2023,
            ],
            '26' => [
                'temperature' => -14,
                'year' => 2018,
            ],
            '05' => [
                'temperature' => -12,
                'year' => 2023,
            ],
            '38' => [
                'temperature' => -14,
                'year' => 2018,
            ],
            '57' => [
                'temperature' => -14,
                'year' => 2021,
            ],
            '17' => [
                'temperature' => -17,
                'year' => 2018,
            ],
            '24' => [
                'temperature' => -17,
                'year' => 2018,
            ],
            '33' => [
                'temperature' => -17,
                'year' => 2018,
            ],
            '47' => [
                'temperature' => -17,
                'year' => 2018,
            ],
            '27' => [
                'temperature' => -12,
                'year' => 2013,
            ],
            '76' => [
                'temperature' => -12,
                'year' => 2013,
            ],
            '95' => [
                'temperature' => -12,
                'year' => 2013,
            ],
            '51' => [
                'temperature' => -13,
                'year' => 2013,
            ],
            '67' => [
                'temperature' => -14,
                'year' => 2013,
            ],
            '07' => [
                'temperature' => -15,
                'year' => 2018,
            ],
            '43' => [
                'temperature' => -15,
                'year' => 2018,
            ],
            '08' => [
                'temperature' => -14,
                'year' => 2013,
            ],
            '54' => [
                'temperature' => -16,
                'year' => 2017,
            ],
            '55' => [
                'temperature' => -14,
                'year' => 2013,
            ],
            '25' => [
                'temperature' => -16,
                'year' => 2017,
            ],
            '70' => [
                'temperature' => -16,
                'year' => 2017,
            ],
            '88' => [
                'temperature' => -16,
                'year' => 2017,
            ],
            '74' => [
                'temperature' => -15,
                'year' => 2017,
            ],
            '73' => [
                'temperature' => -15,
                'year' => 2017,
            ],
            '02' => [
                'temperature' => -15,
                'year' => 2013,
            ],
            '59' => [
                'temperature' => -15,
                'year' => 2013,
            ],
            '60' => [
                'temperature' => -15,
                'year' => 2013,
            ],
            '62' => [
                'temperature' => -15,
                'year' => 2013,
            ],
            '80' => [
                'temperature' => -15,
                'year' => 2013,
            ],
            '30' => [
                'temperature' => -16,
                'year' => 2018,
            ],
            '34' => [
                'temperature' => -16,
                'year' => 2018,
            ],
            '48' => [
                'temperature' => -16,
                'year' => 2018,
            ],
            '68' => [
                'temperature' => -17,
                'year' => 2021,
            ],
            '12' => [
                'temperature' => -16,
                'year' => 2013,
            ],
            '15' => [
                'temperature' => -16,
                'year' => 2013,
            ],
            '19' => [
                'temperature' => -16,
                'year' => 2013,
            ],
            '46' => [
                'temperature' => -16,
                'year' => 2013,
            ],
        ];

        return $minimalTemperatureByDepartment[$departement];
    }
}