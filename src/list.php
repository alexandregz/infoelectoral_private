<?php
/**
 * infoelectoral, intérprete de microdatos electorales del Ministerio del Interior español.
 * Copyright (C) 2020 Jaime Gómez-Obregón
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @copyright     Copyright (c) Jaime Gómez-Obregón
 * @link          https://github.com/JaimeObregon/infoelectoral
 * @license       https://www.gnu.org/licenses/agpl-3.0.en.html
 */

require 'includes/functions.php';

/**
 * Dados dos ficheros, uno con las candidaturas (`03*.DAT`) y otro con los candidatos (`04*.DAT`),
 * este script los interpreta y combina devolviendo por `stdout` un fichero CSV con las listas
 * electorales de dicho proceso electoral.
 *
 * Ni que decir tiene que los dos ficheros dados han de pertenecer al mismo proceso electoral.
 */

// Algunos ficheros particularmente grandes requieren más memoria de la predeterminada
ini_set('memory_limit', '1G');

if (count($argv) != 3) {
    printf("Uso: %s [03*.DAT] [04*.DAT]\n", $argv[0]);
    die;
}

$candidaturas = $candidatos = [];

// Interpreta el nombre del fichero únicamente
$file = parseName($argv[1]);

/**
 * Para la decodificación de los municipios la especificación oficial remite al INE.
 * Pero los códigos cambian a comienzos de cada año, por lo que se hace preciso cargar
 * la del año correspondiente.
 * 
 * Y además es precesio añadir a la correspondencia los códigos que el Ministerio ha utilizado
 * históricamente pero que el INE actualmente no reconoce.
 */
require sprintf('includes/municipios/%s.php', $file['Año'] >= 2001 ? $file['Año'] : '2001');
const MUNICIPIOS = MUNICIPIOS_INE + MUNICIPIOS_INEXISTENTES;

// Interpreta el contenido del fichero de candidaturas
$results = parseFile('03', $argv[1]);
foreach ($results as &$result) {
	$codigo = $result['Código'];
	$candidaturas[$codigo] = [
		'Siglas' => $result['Siglas'],
		'Candidatura' => $result['Candidatura'],
		'Provincial' => $result['Candidatura provincial'],
		'Autonómica' => $result['Candidatura autonómica'],
		'Nacional' => $result['Candidatura nacional'],
	];
}

// Interpreta el contenido del fichero de candidatos
$results = parseFile('04', $argv[2]);
foreach ($results as &$result) {
	$nombre = implode(' ',  [
		$result['Nombre'],
		$result['Primer apellido'],
		$result['Segundo apellido'] ?? '',
	]);

	$candidatura = $result['Candidatura'];

	$candidato = [
		'Candidato' => $nombre,
		'Provincia' => $result['Provincia'],
		'Municipio' => $result['Municipio'],
		'Siglas' => $candidaturas[$candidatura]['Siglas'] ?? null,
		'Candidatura' => $candidaturas[$candidatura]['Candidatura'] ?? null,
		'Número de orden' => $result['Número de orden del candidato'],
		'Sexo' => $result['Sexo'],
		'Elegido' => $result['Elegido'],
	];
	$candidatos[] = $candidato;
}

print_r($candidatos);
die;

// Escribe en `stdout` los resultados en formato CSV
$output = fopen('php://output', 'w');
foreach ($candidatos as $candidato) {
	fputcsv($output, array_values($candidato));
}
fclose($output);