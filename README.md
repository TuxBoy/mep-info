# Mep info

Des scripts PHP CLI qui ont pour but d'envoyer les informations de la dernières MEP (Mise en production)
sur un channel discord.

Il y a aussi une commande qui permet de récupérer les logs PHP et de les afficher dans un channel discord

## Installtion

```bash
$ git clone https://github.com/TuxBoy/mep-info.git && cd mep-info
$ composer install
``` 

## Usage

```bash
$ php console.php mep [branch]
```

C'est la commande qui lance la mise en production :
* [branch] : La branche Git a MEP, paramètre facultatif (master par défaut).

