# language: fr

@feature/get
Fonctionnalité: Récupère le contenu d'un ou plusieurs fichiers

Scénario: Récupérer le contenu d'un fichier
    Quand je veux récupérer le contenu du fichier "IDV-OPTD/003/quest/myCRD/conf.ini" situé dans "activities"
    Alors le résultat devrait être identique au fichier "conf.ini"

Scénario: Récupérer le contenu d'un fichier qui n'existe pas
    Quand je veux récupérer le contenu du fichier "IDV-OPTD/003/quest/myCRD/noexist.blaaaa" situé dans "activities"
    Alors je devrais avoir une exception "Not Found"
