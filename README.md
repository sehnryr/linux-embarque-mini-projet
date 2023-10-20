# Linux Embarqué - Mini-projet

Le but de ce mini-projet est de réaliser une interface web de contrôle des LEDs
de la carte BeagleBone mise à disposition en TP.

## Dépendences
Pour ce projet, nous avons utilisé les dépendences suivantes :
- [nginx](https://nginx.org/en/) : serveur web
- [php](https://www.php.net/) : langage de programmation
- [jquery](https://jquery.com/) : librairie javascript

## Installation
Pour installer les dépendences, il faut d'abord mettre à jour les références
des paquets disponibles :
```bash
apt update
```

Ensuite, nous pouvons installer les paquets (ou les mettre à jour si déjà 
installés)
```bash
apt install nginx php-fpm
```

Il nous faut ensuite installer la librairie jquery dans le dossier
`/var/www/html`. Pour cela, nous avons utilisé la version 3.7.1 disponible sur
le site officiel :
```bash
cd /var/www/html
wget https://code.jquery.com/jquery-3.7.1.min.js
```

## Configuration
La configuration de nginx se fait dans le fichier 
`/etc/nginx/sites-available/default`. Par défaut, nginx est déjà installé sur la
BeagleBone et une configuration est déjà présente dans ce fichier. Dans notre
cas, nous avons modifié la configuration de la section `server` qui écoute sur
le port 8080. La configuration de cette section est la suivante :
```nginx
server {
    listen 8080 default_server;
    listen [::]:8080 default_server;

    root /var/www/html;

    index index.html;

    # Cette section permet de servir les fichiers statiques
    location / {
        try_files $uri $uri/ =404;
    }

    # Cette section permet d'intéragir avec le serveur php-fpm et interpréter
    # les fichiers php
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        # La version de php-fpm peut être différente selon la version de php,
        # mais dans notre cas, la dernière version disponible sur les dépôts
        # est la 7.3
        fastcgi_pass unix:/var/run/php/php7.3-fpm.sock;
    }
}
```

Il est également nécessaire d'ajouter le groupe `gpio` à l'utilisateur
`www-data` pour que le serveur web puisse intéragir avec les GPIOs de la carte
BeagleBone :
```bash
usermod -a -G gpio www-data
```

Il faut ensuite redémarrer le serveur nginx et php-fpm pour que les
modifications soient prises en compte :
```bash
systemctl restart nginx
systemctl restart php7.3-fpm
```

## Exemple
Le développement de l'interface web se fait dans le dossier `/var/www/html`.

Dans notre cas, nous avons développé une interface web permettant de contrôler
les LEDs de la carte BeagleBone. Cette interface est composée de plusieurs
fichiers :
- `index.html` : page web
- `jquery-3.7.1.min.js` : librairie jquery
- `get_button_state.php` : script php permettant de récupérer l'état du bouton
  poussoir
- `get_potentiometer_value.php` : script php permettant de récupérer la valeur
- `get_led3_state.php` : script php permettant de récupérer l'état de la LED 3
- `get_led2_state.php` : script php permettant de récupérer l'état de la LED 2
- `get_ledG_state.php` : script php permettant de récupérer l'état de la LED
  verte
- `get_ledB_state.php` : script php permettant de récupérer l'état de la LED
- `get_ledR_state.php` : script php permettant de récupérer l'état de la LED
  rouge
- `set_led3_state.php` : script php permettant de modifier l'état de la LED 3
- `set_led2_state.php` : script php permettant de modifier l'état de la LED 2
- `set_ledR_state.php` : script php permettant de modifier l'état de la LED
  rouge
- `set_ledG_state.php` : script php permettant de modifier l'état de la LED
- `set_ledB_state.php` : script php permettant de modifier l'état de la LED
  bleue

Dans cet exemple nous ne montrons que le code de la page web `index.html` et
des scripts php `get_potentiometer_value.php` et `set_led3_state.php`. Les 
autres scripts php sont similaires à ceux-ci.

Le fichier `index.html` a besoin de 3 parties pour fonctionner :
- La librairie jquery
- Le code html
- Le code javascript

La librairie jquery est incluse dans `<head>` le fichier `index.html` avec la
ligne suivante :
```html
<script src="jquery-3.7.1.min.js"></script>
```

### Contrôle de la led 3
L'état de la led 3 est contrôlé par l'élément `<input>` de type `checkbox` :
```html
<input type="checkbox" name="led3" />
```

Le code javascript permet de récupérer l'état de la led 3 et de la modifier
lorsque l'utilisateur clique sur l'élément `<input>` :
```javascript
$('input[type="checkbox"]').click(function() {
    var led = $(this).attr("name");
    var state = $(this).prop("checked") ? 1 : 0;

    var file = "set_" + led + "_state.php";

    $.ajax({
        url: file,
        method: 'POST',
        data: {
            state: state
        },
    });
});
```

L'utilité de jquery est de simplifier le code javascript. En effet, sans
jquery le code javascript serait beaucoup plus long et complexe.

Le script php `set_led3_state.php` permet de modifier l'état de la led 3. Voici
son code :
```php
<?php
$state = $_POST['state'];
$filename = "/sys/class/leds/beaglebone:green:usr3/brightness";
$file = fopen($filename, "w") or die("Impossible d'ouvrir le fichier");
fwrite($file, $state);
fclose($file);
```

Ce script récupère l'état de la led 3 dans la variable `$_POST['state']` et
l'écrit dans le fichier `/sys/class/leds/beaglebone:green:usr3/brightness`.

### Lecture de la valeur du potentiomètre
Pour récupérer l'état du potentiomètre, nous avons utilisé une méthode
similaire.

Le code html permettant d'afficher la valeur du potentiomètre est le suivant :
```html
<p>Valeur du potentiomètre : <span id="valeur_potentiometre"></span></p>
```

Le script php `get_potentiometer_value.php` permet de récupérer la valeur du
potentiomètre. Voici son code :
```php
<?php
$filename = "/sys/bus/iio/devices/iio:device0/in_voltage3_raw";
$file = fopen($filename, "r") or die("Unable to open file!");
echo fread($file, filesize($filename));
fclose($file);
```

Ce script lit la valeur du potentiomètre dans le fichier
`/sys/bus/iio/devices/iio:device0/in_voltage3_raw` et l'affiche sur la page
web grâce au code javascript suivant :
```javascript
setInterval(function() {
    $.ajax({
        url: 'get_potentiometer_value.php',
        method: 'GET',
        success: function(data) {
            $('#valeur_potentiometre').html(data);
        }
    });
}, 50);
```

Ce code javascript permet de mettre à jour la valeur du potentiomètre toutes
les 50ms.

Le code complet de ce projet est disponible sur le dépôt git suivant :
https://github.com/sehnryr/linux-embarque-mini-projet
