# Como crear ficheiros para proxecto de mapas_electorais

## Proxecto paralelo

https://github.com/alexandregz/mapas_electorais (público)

https://github.com/alexandregz/bngcomarca (privado e funcionando)


## HOWTO



### 1

- Crear ficheiro de municipios en `src/includes/municipios/YYYY.php`: abonda con copiar `2020.php` a `2023.php` se non houbera cambios.

- Xerar csv para `bngcomarca/datos/eleccions_mesas/congreso/` (todos os partidos):
```bash
alex@vosjod:/Volumes/Seagate Expansion 1/BNG Ames mapas/infoelectoral_private(main)$ /Applications/MAMP/bin/php/php8.0.8/bin/php src/creaCsvMesas.php --ficheiro files/congreso/02202307_MESA/10022307.DAT -c Galiza > '/Users/alex/Desktop/Ames politica/BNG_Ames/MAPAS_tereborace/mapas_toda_a_comarca_2022-07-03/bngcomarca/datos/eleccions_mesas/congreso/datos_congreso_2023-07_galiza.csv'
Buscando resultados Congreso (7/2023) de [] en [Galicia] ...
processando ficheiro de candidaturas (03022307.DAT)...
processando ficheiro de resultados por mesas (10022307.DAT)...
	Totais mesas: 35815
processando ficheiro de resultados totais (09022307.DAT)...
	Totais resultados: 3966
alex@vosjod:/Volumes/Seagate Expansion 1/BNG Ames mapas/infoelectoral_private(main)$
```

- Comprobar candidaturas do BNG:

```bash
alex@vosjod:/Volumes/Seagate Expansion 1/BNG Ames mapas/infoelectoral_private(main)$ /Applications/MAMP/bin/php/php8.0.8/bin/php src/creaCsvMesas.php --ficheiro files/congreso/02202307_MESA/10022307.DAT -c Galiza --ver_candidaturas|fgrep -i Bloque -C4
Buscando resultados Congreso (7/2023) de [] en [Galicia] ...
processando ficheiro de candidaturas (03022307.DAT)...
            [Año] => 2023
            [Mes] => 7
            [Código] => 000032
            [Siglas] => NC-bc
            [Candidatura] => NUEVA CANARIAS - BLOQUE CANARISTA
            [Candidatura provincial] => 000032
            [Candidatura autonómica] => 000032
            [Candidatura nacional] => 000032
        )
--
--
            [Año] => 2023
            [Mes] => 7
            [Código] => 000062
            [Siglas] => BQEX
            [Candidatura] => BLOQUE EXTREME?O
            [Candidatura provincial] => 000062
            [Candidatura autonómica] => 000062
            [Candidatura nacional] => 000062
        )
--
--
            [Año] => 2023
            [Mes] => 7
            [Código] => 000065
            [Siglas] => B.N.G.
            [Candidatura] => BLOQUE NACIONALISTA GALEGO
            [Candidatura provincial] => 000065
            [Candidatura autonómica] => 000065
            [Candidatura nacional] => 000065
        )
```


### 2

- Baixar o xlsx de infor electoral: https://infoelectoral.interior.gob.es/es/elecciones-celebradas/area-de-descargas/ Área de Descargas / Otras Descargas / Datos de Municipios / Descargar 2023 (buscando en buscador)

- Empregar:

```bash
alex@vosjod:~/Desktop/Ames politica/BNG_Ames/MAPAS_tereborace/mapas_toda_a_comarca_2022-07-03/bngcomarca/infoelectoralgob(main)$ /Applications/MAMP/bin/php/php8.0.8/bin/php index.php ~/Desktop/02_202307_1.xlsx ../datos/congreso/202307_concelhos_congreso.csv -v
............
...
```



### 3

- Volvemos empregar `creaCsvMesas`, para Santiago:

```bash
alex@vosjod:/Volumes/Seagate Expansion 1/BNG Ames mapas/infoelectoral_private(main)$ /Applications/MAMP/bin/php/php8.0.8/bin/php src/creaCsvMesas.php --ficheiro files/congreso/02202307_MESA/10022307.DAT --concello 'Santiago de Compostela' -c Galiza > '/Users/alex/Desktop/Ames politica/BNG_Ames/MAPAS_tereborace/mapas_toda_a_comarca_2022-07-03/bngcomarca/santiago/datos_congreso_2023-07_SantiagoCompostela.csv'
Buscando resultados Congreso (7/2023) de [] en [Galicia] (Concello: Santiago de Compostela) ...
processando ficheiro de candidaturas (03022307.DAT)...
processando ficheiro de resultados por mesas (10022307.DAT)...
	Totais mesas: 1044
processando ficheiro de resultados totais (09022307.DAT)...
	Totais resultados: 123
alex@vosjod:/Volumes/Seagate Expansion 1/BNG Ames mapas/infoelectoral_private(main)$
```


### 4

- Os datos de Ames, (ver no raiz `bng_ames.php`), están extraídos copiando e pegando dos excels que as funcionarias do concello facilitan.

Hai que preservar a orde que se ten de anteriores convocatorias.