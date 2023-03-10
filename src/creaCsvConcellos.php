<?php
/**
 * O mesmo que creaCsvMesas.php pero por concelhos.
 * Nom hai ficheiros 10*.DAT, se nom 05* (Fichero de datos globales de ámbito municipal) e 06* (Fichero de datos de candidaturas de ámbito municipal.)
 * 
 * HELP:
 * alex@vosjod:/Volumes/Seagate Expansion 1/BNG Ames mapas/infoelectoral(master)$ php src/creaCsvConcellos.php --help
 * Uso: php src/creaCsvMesas.php
 * 		-f/--ficheiro PATH/FICHEIRO_10*.DAT
 * 		-c/--comunidade COMUNIDADE
 * 		-p/--partido SIGLAS_PARTIDO|ID_CANDIDATURA_NACIONAL
 * 		[-s/--saida FICHEIRO_SAIDA_CSV]
 * 		[--ver_candidaturas]
 * 		[-h/--help]
 * 
 * A docu é a mesma que creaCsvMesas.php, ver nese ficheiro.
 */


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
 *
 * Consulta `parse.php` para explorar el contenido de cualquier fichero `.DAT`.
 */

// Algunos ficheros particularmente grandes requieren más memoria de la predeterminada
ini_set('memory_limit', '-1');


//---- menu
$short_options = "f:c:p:s::vh";
$long_options = ["ficheiro:", "comunidade:", "partido:", "concello:", "saida::", "ver_candidaturas", "help"];
$options = getopt($short_options, $long_options);

// variables principais desde args
$FICHEIRO = isset($options['ficheiro']) ? $options['ficheiro']: (isset($options['f']) ? $options['f'] : null) ;
$COMUNIDADE = isset($options['comunidade']) ? $options['comunidade']: (isset($options['c']) ? $options['c'] : null );
$PARTIDO = isset($options['partido']) ? $options['partido']: (isset($options['p']) ? $options['p'] : null );
$SAIDA = isset($options['saida']) ? $options['saida']: (isset($options['s']) ? $options['s'] : null );

// só long option
$CONCELLO = isset($options['concello']) ? $options['concello']: null;

if(isset($options['h']) || isset($options['help']) || !isset($FICHEIRO)) {
	exit("Uso: php {$argv[0]} 
		-f/--ficheiro PATH/FICHEIRO_06*.DAT 
		-c/--comunidade COMUNIDADE
		-p/--partido SIGLAS_PARTIDO|ID_CANDIDATURA_NACIONAL
		[--concello CONCELLO(case sensitive)]
		[-s/--saida FICHEIRO_SAIDA_CSV] 
		[--ver_candidaturas] 
		[-h/--help]\n");
}
//----

$candidaturas = $resultados_por_mesas = $limite_resultados_comunidade = [];

$VER_CANDIDATURAS = isset($options['ver_candidaturas']) ? true : false;

// por se introduzo "galiza", traduzo ao empregado em código
if(isset($COMUNIDADE)) {
	if(strtolower($COMUNIDADE) == 'galiza') $COMUNIDADE = 'galicia';
}


$datos_ficheiros = pathinfo($FICHEIRO);
$file_candidaturas = substr_replace($datos_ficheiros['basename'], '03', 0, 2);
$file_totais = substr_replace($datos_ficheiros['basename'], '05', 0, 2);
$file_concelhos = $datos_ficheiros['basename'];

$file_candidaturas_absolute_path = $datos_ficheiros['dirname']."/$file_candidaturas";
$file_totais_absolute_path = $datos_ficheiros['dirname']."/$file_totais";



// Interpreta el nombre del fichero únicamente
$file 		= parseName($file_candidaturas_absolute_path);
$resultados = parseName($FICHEIRO);
if($resultados['Código'] != '06') {
	die("De primeiro argumento debe ser un ficheiro de resultados por mesas (06*.DAT)\n");
}




$siglas = isset($PARTIDO) ? $PARTIDO : null;
$msg = "Buscando resultados ".$file['Tipo']." (".$file['Mes']."/".$file['Año'].") de [$siglas]";
if(isset($COMUNIDADE)) {
	$id_comunidade = array_search(strtolower($COMUNIDADE), array_map('strtolower', AUTONOMIAS));
	$msg .= " en [".AUTONOMIAS[$id_comunidade]."]";

	$limite_resultados_comunidade = ['Comunidad autónoma' => AUTONOMIAS[$id_comunidade]];
	if($CONCELLO) {
		$limite_resultados_comunidade['Municipio'] = $CONCELLO;
		$msg .= " (Concello: $CONCELLO)";
	}
}
fwrite(STDERR, $msg." ...\n");



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



fwrite(STDERR, "processando ficheiro de candidaturas ($file_candidaturas)...\n");
$results = parseFile('03', $file_candidaturas_absolute_path);
foreach ($results as &$result) {
	// so das siglas recibidas. Se som numericas, miramos contra 'Candidatura nacional'
	if($siglas) {
		if($result['Siglas'] != $siglas) {
			if(is_numeric($siglas)) {
				if($result['Candidatura nacional'] != $siglas) {
					continue;
				}
			}
			else {
				continue;
			}
		}
	}

	// se hai $PARTIDO so deberia haber um resultado e abaixo tiramos contra $candidaturas[0].
	// Se hai máis, facilitamos buscar as siglas ponhendo de key o codigo que despois estar em results (ver ficheiros 10*.dat)
	if($PARTIDO) {
		$candidaturas[] = $result;
	}
	else {
		$candidaturas[$result['Código']] = $result;
	}
}
// debug porque durante distintas eleicçons os nomes dos partidos podem mudar, assi que fazemos output para poder rapidamente comprobar que nome empregar
if($VER_CANDIDATURAS) {
	print_r($candidaturas);die();
}


set_error_handler(function($errno, $errstr, $errfile, $errline) {
    
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// restore_error_handler();


fwrite(STDERR, "processando ficheiro de resultados por Concelhos ($file_concelhos)...\n");
$results = parseFile('06', $FICHEIRO, true, $limite_resultados_comunidade);	// tamem necesito os datos em bruto aqui
fwrite(STDERR, "\tTotais mesas: ".count($results)."\n");


fwrite(STDERR, "processando ficheiro de resultados totais ($file_totais)...\n");
// para ter o total da mesa, temos que cruzar estes datos com results, así que imos agrupar por [provincia-municipio-distrito-seccion-mesa]
$totals = parseFile('05', $file_totais_absolute_path, true, $limite_resultados_comunidade);
fwrite(STDERR, "\tTotais resultados: ".count($totals)."\n");



// Agora creamos array cumha identificable por mesa (ProvMun|DistritoSeccionMesa) e coas keys que temos que engadir a RESULTADOS por mesas (do 10*)
$totais = [];
foreach($totals as $t) {
	// construimos a key para que seja única e identifique á mesa univocamente
	// falta código de distrito ás veces, correxido direitamente em functions.php, asi como mesas, por discrepancia co formateado :-) (devolver null em mesas 'U', p.e.)
	$key = createKeyIdentifyCouncil($t);

	$censo = isset($t['Censo de escrutinio']) ? $t['Censo de escrutinio'] : $t['Censo CERA'];

	$totais[$key] = [
		'censo' => $censo,
		'branco' => $t['Votantes en blanco'],
		'nulos' => $t['Votantes nulos'],
		'votos candidaturas' => $t['Votantes a candidaturas'],
		'votos totais' => ($t['Votantes a candidaturas'] + $t['Votantes en blanco'] + $t['Votantes nulos']),
	];
}
unset($totals);
// print_r($totais);die();



set_error_handler(function($errno, $errstr, $errfile, $errline) {
    
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// restore_error_handler();



// imos gardar os resultados dos votos dos partidos num array, posteriormente imolo engadir a $resultados_por_mesas ordeado
$partidosResultados = $siglasArr = [];

// Aqui cruzamos Resultados por mesas cos Totais globais (Votos totais están no 09*, o resto está no 10*)
foreach ($results as $r) {
	$key = createKeyIdentifyCouncil($r);
	// echo "$key ==> ".$candidaturas[$r['Código de candidatura']]['Siglas']." = ".$r['Votos obtenidos']."\n";continue;

	// no Congreso os listados podem ser grandes, limitamos aos que tenhem votos na mesa
	if($r['Votos obtenidos'] == 0) {
		continue;
	}

	// partido do actual resulset (votos de partidos por cada mesa)
	$partido = '';
	
	// se pasarom partido político para buscar, só resultados dese
	if($PARTIDO) {
		if($r['Código de candidatura'] != $candidaturas[0]['Candidatura nacional'] ) {
			continue;
		}
		$partido = $candidaturas[0]['Siglas'];
	}
	// 'hack': se nom pasarom partidos, engadimos SÓ os que levarom votos, para nom fazer inabarcable o listado de partidos por mesa (em Congreso, por exemplo)
	else {
		if($r['Votos obtenidos'] == 0) {
			continue;
		}
		// hack: para algunhas eleicçons, o PSdeG-PSOE (código 000091) aparece na província de Lugo como votos ao PSOE (código 000093), polo que desquadra todo.
		//			Colhendo de siglas as da "Autonómica", que é o 91, agrupamos todos.
		// 			Fazemos isto sempre que se pasara COMUNIDADE. A melhor soluçóm? realmente cambiar os datos.
		$partido = $candidaturas[$r['Código de candidatura']]['Siglas'];
		if($COMUNIDADE && $candidaturas[$r['Código de candidatura']]['Candidatura autonómica'] != $r['Código de candidatura']) {
			$codigo_autonomica = $candidaturas[$r['Código de candidatura']]['Candidatura autonómica'];
			$partido = $candidaturas[$codigo_autonomica]['Siglas'];
		}
		//echo "partido: [$partido] -- ".$r['Código de candidatura']."-".$candidaturas[$r['Código de candidatura']]['Candidatura autonómica']."\n";
	}

	if(key_exists($key, $totais)) {
		// engadimos os datos comuns se nom existem em cada mesa
		if(!key_exists($key, $resultados_por_mesas)) {
			$cod_municipio = isset($r['Municipio_raw']) ? (int)$r['Municipio_raw']: $r['CERA_raw'];
			$municipio = isset($r['Municipio']) ? $r['Municipio']: 'CERA';
			$seccion = isset($r['Código de sección']) ? (int)trim($r['Código de sección']) : '';
			$distrito = isset($r['Número de distrito']) ? (int)$r['Número de distrito'] : '';
			$mesa = isset($r['Código de mesa']) ? $r['Código de mesa'] : '';

			$resultados_por_mesas[$key] = [
				'cod_provincia' => $r['Provincia_raw'],
				'provincia' => $r['Provincia'],
				'cod_municipio' => $cod_municipio,
				'municipio' => $municipio,
				'distrito' => $distrito,
				'seccion' => $seccion,
				'mesa' => $mesa,
	
				'censo' => $totais[$key]['censo'],
				// 'votos candidaturas' => $totais[$key]['votos candidaturas'],
				'votos totais' => $totais[$key]['votos totais'],
				'branco' => $totais[$key]['branco'],
				'nulos' => $totais[$key]['nulos'],
			];
		}
		// imos conservar os votos na mesa para o partido por
		$partidosResultados[$key][$partido] = $r['Votos obtenidos'];

		// gardo todos os partidos de todas as mesas, porque o csv vai ter que ter todos os partidos,
		// ainda que nom tenham votos noutras mesas, pero se tenhem nalgumha qualquera tenhem que aparecer.
		// 		hack: imos ordear polo numero de votos, asi que imos contabilizando as aparicións
		if(isset($siglasArr[$partido])) {
			$siglasArr[$partido] += $r['Votos obtenidos'];
		}
		else {
			$siglasArr[$partido] = $r['Votos obtenidos'];
		}
		
	}
}


// ordeamos por valor descendente de maior a menor. Se queremos outra ordeacion poderiamos empregar uasort() ou similares
arsort($siglasArr);


// engadimos os resultados dos partidos (siglas) ordeados: a cada linha de resultados engadimoslhe os datos dos partidos, ordeados, segundo a key da mesa.
foreach($resultados_por_mesas as $key => $datos) {
	foreach($siglasArr as $partido => $votosTotais) {
		// comprobo se hai votos na mesa dese partido para evitar warnings
		$resultados_por_mesas[$key][$partido] = isset($partidosResultados[$key][$partido]) ? $partidosResultados[$key][$partido] : 0;
	}
}



//--- output (a stdout ou a ficheiro se pasamos --saida=/-s=)
$output = fopen('php://output', 'w');
if($SAIDA) {
	$output = fopen($SAIDA, 'w');
}

// para as cabeceiras ao ser multidimensional colho o primeiro dos values (que som outro array cos datos por mesa)
fputcsv($output, array_keys(array_values($resultados_por_mesas)[0]), ";");
foreach ($resultados_por_mesas as $r) {
	fputcsv($output, $r, ";");
}
fclose($output);
