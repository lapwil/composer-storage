# composer-storage
Silex provider for file storage

[![Build Status](http://drone.etna-alternance.net/api/badge/github.com/etna-alternance/composer-storage/status.svg?branch=master)](http://drone.etna-alternance.net/github.com/etna-alternance/composer-storage)

Après clone du dépot, se mettre à la racine et :
================================================

 * composer install

Pour que les tests fonctionnent, il faut :
==========================================

* Il faut php56
* brew tap homebrew/nginx
* brew install nginx-full --with-gunzip --with-webdav
* sed -i '' -e "s#\$PWD#$PWD#g" $PWD/Tests/Functional/bootstrap/nginx.conf

Pour lancer les tests:
=====================
juste behat `APPLICATION_ENV='testing' composer behat`
tout        `APPLICATION_ENV='testing' composer phing`

commande all in one :
=====================
 ```
 composer install && APPLICATION_ENV='testing' composer phing
 ```

Pour l'utiliser en tant que provider :
======================================
À ajouter dans le bower.json
```
"etna/composer-storage": "1.x"
```
