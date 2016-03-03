# language: fr

@feature/delete
Fonctionnalité: Supprime un fichier

Scénario: Supprimer un fichier
    Quand je veux supprimer le fichier "IDV-OPTD/003/quest/myCRD/conf.ini" situé dans "activities"
    Alors la réponse devrait être "OK"

Scénario: Supprimer un fichier qui n'existe pas
    Quand je veux supprimer le fichier "IDV-OPTD/003/quest/myCRD/noexist.blaaaa" situé dans "activities"
    Alors je devrais avoir une exception "Not Found"
