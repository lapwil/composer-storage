# composer-storage
Silex provider for file storage

[![Build Status](http://drone.etna-alternance.net/api/badge/github.com/etna-alternance/composer-storage/status.svg?branch=master)](http://drone.etna-alternance.net/github.com/etna-alternance/composer-storage)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/etna-alternance/composer-storage/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/etna-alternance/composer-storage/?branch=master)
[![Coverage Status](https://coveralls.io/repos/github/etna-alternance/composer-storage/badge.svg?branch=master)](https://coveralls.io/github/etna-alternance/composer-storage?branch=master)

Après clone du dépot, se mettre à la racine et :
================================================

 * composer install

Pour que les tests fonctionnent, il faut :
==========================================

* Il faut php55
* brew tap homebrew/nginx
* brew install nginx-full --with-gunzip --with-webdav

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
