# composer-storage
Silex provider for file storage

Après clone du dépot, se mettre à la racine et :
================================================

 * composer install

Pour que les tests fonctionnent, il faut :
==========================================

* Il faut php56
* brew tap homebrew/nginx
* brew install nginx-full --with-gunzip --with-webdav
* sed -i '' -e "s#\$HOME#$HOME#g" $HOME/ETNA/composer-storage/Tests/Functional/bootstrap/nginx.conf

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
