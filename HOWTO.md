# Como crear ficheiros para proxecto de mapas_electorais

## Proxecto paralelo

https://github.com/alexandregz/mapas_electorais (público)

https://github.com/alexandregz/bngcomarca (privado e funcionando)


## HOWTO


1. Crear ficheiro de municipios en `src/includes/municipios/YYYY.php`: abonda con copiar `2020.php` a `2023.php` se non houbera cambios.


2. Xerar csv:
```php
alex@vosjod:/Volumes/Seagate Expansion 1/BNG Ames mapas/infoelectoral_private(main)$ /Applications/MAMP/bin/php/php8.0.8/bin/php src/creaCsvMesas.php --ficheiro files/congreso/02202307_MESA/10022307.DAT -c Galiza > '/Users/alex/Desktop/Ames politica/BNG_Ames/MAPAS_tereborace/mapas_toda_a_comarca_2022-07-03/bngcomarca/datos/eleccions_mesas/congreso/datos_congreso_2023-07_galiza.csv'
Buscando resultados Congreso (7/2023) de [] en [Galicia] ...
processando ficheiro de candidaturas (03022307.DAT)...
processando ficheiro de resultados por mesas (10022307.DAT)...
	Totais mesas: 35815
processando ficheiro de resultados totais (09022307.DAT)...
	Totais resultados: 3966
alex@vosjod:/Volumes/Seagate Expansion 1/BNG Ames mapas/infoelectoral_private(main)$
```