<?php
/**
 * Exemplo ultimas eleicçons Congreso (NOTA: emprego o PHP de MAMP direitamente):
 * 
 * alex@vosjod:/Volumes/Seagate Expansion 1/BNG Ames mapas/infoelectoral_private(main)$ /Applications/MAMP/bin/php/php8.0.8/bin/php src/creaCsvMesas.php --ficheiro files/congreso/02202307_MESA/10022307.DAT -c Galiza > '/Users/alex/Desktop/Ames politica/BNG_Ames/MAPAS_tereborace/mapas_toda_a_comarca_2022-07-03/bngcomarca/datos/congreso/202307_concelhos_congreso.csv'
 * Buscando resultados Congreso (7/2023) de [] en [Galicia] ...
 * processando ficheiro de candidaturas (03022307.DAT)...
 * processando ficheiro de resultados por mesas (10022307.DAT)...
 * 	Totais mesas: 35815
 * processando ficheiro de resultados totais (09022307.DAT)...
 * 	Totais resultados: 3966
 * alex@vosjod:/Volumes/Seagate Expansion 1/BNG Ames mapas/infoelectoral_private(main)$
 * 
 * 
 * HELP:
 * alex@vosjod:/Volumes/Seagate Expansion 1/BNG Ames mapas/infoelectoral(master)$ php src/creaCsvMesas.php --help
 * Uso: php src/creaCsvMesas.php
 * 		-f/--ficheiro PATH/FICHEIRO_10*.DAT
 * 		-c/--comunidade COMUNIDADE
 * 		-p/--partido SIGLAS_PARTIDO|ID_CANDIDATURA_NACIONAL
 * 		[-s/--saida FICHEIRO_SAIDA_CSV]
 * 		[--ver_candidaturas]
 * 		[-h/--help]
 * 
 * 
 * GARDAR DATOS DIREITAMENTE EM .csv:
 * alex@vosjod:/Volumes/Seagate Expansion 1/BNG Ames mapas/infoelectoral(master)$ php src/creaCsvMesas.php --ficheiro files/congreso/02201911_MESA/10021911.DAT -p BNG -c Galiza --saida=datos.csv
 * Buscando resultados Congreso (11/2019) de [BNG] en [Galicia] ...
 * processando ficheiro de candidaturas (03021911.DAT)...
 * processando ficheiro de resultados por mesas (10021911.DAT)...
 * 	Totais mesas: 46491
 * processando ficheiro de resultados totais (09021911.DAT)...
 * 	Totais resultados: 3960
 * alex@vosjod:/Volumes/Seagate Expansion 1/BNG Ames mapas/infoelectoral(master)$ head datos.csv
 * cod_provincia;provincia;cod_municipio;municipio;distrito;seccion;mesa;censo;"votos totais";branco;nulos;BNG
 * 15;"A Coruña";1;Abegondo;3;2;U;812;536;13;8;36
 * 15;"A Coruña";2;Ames;3;1;A;608;412;6;3;42
 * 15;"A Coruña";2;Ames;3;9;A;966;585;15;9;62
 * 
 * - Ao separar o output informativo em STDERR e o output do csv em STDOUT, podemos redirixir recibindo infor.
 * - Podemos enviar partido como ID, para os casos nos que o nome tem acentos ou caracteres "raros": 
 * 
 * Recibe:
 * 	- path ao 10*.DAT cos resultados das mesas electorais (vai buscar no mesmo path os ficheiros co mesmo nome 03* e 09* para cruzar datos de candidaturas e resultados totais)
 *  - ID da CA (11 para Galiza, ver FICHEROS.doc)
 *  - partido politico (por exemplo, BNG) para devolver resultados so dese partido
 * 
 * 
 * RESULTADOS SO DUM CONCELLO:
 * alex@vosjod:/Volumes/Seagate Expansion 1/BNG Ames mapas/infoelectoral(master)$ php src/creaCsvMesas.php -f files/congreso/02201911_MESA/10021911.DAT -c galiza -p BNG -s=debug.csv --concello=Ames
 *  Buscando resultados Congreso (11/2019) de [BNG] en [Galicia] (Concello: Ames) ...
 *  processando ficheiro de candidaturas (03021911.DAT)...
 *  processando ficheiro de resultados por mesas (10021911.DAT)...
 *  	Totais mesas: 547
 *  processando ficheiro de resultados totais (09021911.DAT)...
 *  	Totais resultados: 41
 * 
 * 
 * DEBUG:
 * 	para ver candidaturas, por se mudam durante eleicçons:
 *   alex@vosjod:/Volumes/Seagate Expansion 1/BNG Ames mapas/infoelectoral(master)$ php src/creaCsvMesas.php -f 10021911.DAT --ver_candidaturas -p BNG
 *   Buscando resultados Congreso (11/2019) de [BNG] ...
 *   processando ficheiro de candidaturas (03021911.DAT)...
 *   Array
 *   (
 *       [0] => Array
 *           (
 *               [Siglas] => BNG
 *               [Candidatura] => BLOQUE NACIONALISTA GALEGO
 *               [Provincial] => 000010
 *               [Autonómica] => 000010
 *               [Nacional] => 000010
 *           )
 *   
 *   )
 *
 *  alex@vosjod:/Volumes/Seagate Expansion 1/BNG Ames mapas/infoelectoral(master)$ php src/creaCsvMesas.php -f files/europeas/07201905_MESA/10071905.DAT --ver_candidaturas|fgrep BNG -C4
 *  Buscando resultados Parlamento Europeo (5/2019) só de [] en [Galicia] ...
 *  processando ficheiro de candidaturas (03071905.DAT)...
 * 			)
 *  
 *      [44] => Array
 *          (
 *              [Siglas] => BNG-AGORA REPÚBLICAS
 *              [Candidatura] => BLOQUE NACIONALISTA GALEGO-AGORA REPÚBLICAS
 *              [Provincial] => 000045
 *              [Autonómica] => 000045
 *              [Nacional] => 000006
 * 
 * @ToDo pasar candidaturas em numerico (Nacional), ver comentario em 200: 	// so das siglas recibidas. Se som numericas, miramos contra 'Candidatura nacional'
 * 
 * 
 * alex@vosjod:/Volumes/Seagate Expansion 1/BNG Ames mapas/infoelectoral_private(main)$ php src/creaCsvMesas.php -f files/europeas/07201905_MESA/10071905.DAT -c galiza  --concello 'Santiago de Compostela' -s=datos_europeas_2019-07_SantiagoCompostela.csv
 *	Buscando resultados Parlamento Europeo (5/2019) de [] en [Galicia] (Concello: Santiago de Compostela) ...
 *	processando ficheiro de candidaturas (03071905.DAT)...
 * 	processando ficheiro de resultados por mesas (10071905.DAT)...
 *		Totais mesas: 3904
 *	processando ficheiro de resultados totais (09071905.DAT)...
 *		Totais resultados: 122
 *  alex@vosjod:/Volumes/Seagate Expansion 1/BNG Ames mapas/infoelectoral_private(main)$
 * 
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
		-f/--ficheiro PATH/FICHEIRO_10*.DAT 
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
$file_totais = substr_replace($datos_ficheiros['basename'], '09', 0, 2);
$file_mesas = $datos_ficheiros['basename'];

$file_candidaturas_absolute_path = $datos_ficheiros['dirname']."/$file_candidaturas";
$file_totais_absolute_path = $datos_ficheiros['dirname']."/$file_totais";


// Interpreta el nombre del fichero únicamente
$file 		= parseName($file_candidaturas_absolute_path);
$resultados = parseName($FICHEIRO);
if($resultados['Código'] != '10') {
	die("De primeiro argumento debe ser un ficheiro de resultados por mesas (10*.DAT)\n");
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



fwrite(STDERR, "processando ficheiro de resultados por mesas ($file_mesas)...\n");
$results = parseFile('10', $FICHEIRO, true, $limite_resultados_comunidade);	// tamem necesito os datos em bruto aqui
fwrite(STDERR, "\tTotais mesas: ".count($results)."\n");


fwrite(STDERR, "processando ficheiro de resultados totais ($file_totais)...\n");
// para ter o total da mesa, temos que cruzar estes datos com results, así que imos agrupar por [provincia-municipio-distrito-seccion-mesa]
$totals = parseFile('09', $file_totais_absolute_path, true, $limite_resultados_comunidade);
// $totals = parseFile('09', $file_totais_absolute_path, true, ['Comunidad autónoma' => AUTONOMIAS[$id_comunidade], 'Municipio' => 'Ames']);
fwrite(STDERR, "\tTotais resultados: ".count($totals)."\n");



// Agora creamos array cumha identificable por mesa (ProvMun|DistritoSeccionMesa) e coas keys que temos que engadir a RESULTADOS por mesas (do 10*)
$totais = [];
foreach($totals as $t) {
	// construimos a key para que seja única e identifique á mesa univocamente
	// falta código de distrito ás veces, correxido direitamente em functions.php, asi como mesas, por discrepancia co formateado :-) (devolver null em mesas 'U', p.e.)
	$key = createKeyIdentifyBoard($t);

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




// imos gardar os resultados dos votos dos partidos num array, posteriormente imolo engadir a $resultados_por_mesas ordeado
$partidosResultados = $siglasArr = [];

// Aqui cruzamos Resultados por mesas cos Totais globais (Votos totais están no 09*, o resto está no 10*)
foreach ($results as $r) {
	$key = createKeyIdentifyBoard($r);
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

			$resultados_por_mesas[$key] = [
				'cod_provincia' => $r['Provincia_raw'],
				'provincia' => $r['Provincia'],
				'cod_municipio' => $cod_municipio,
				'municipio' => $municipio,
				'distrito' => (int)$r['Número de distrito'],
				'seccion' => $seccion,
				'mesa' => $r['Código de mesa'],
	
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
