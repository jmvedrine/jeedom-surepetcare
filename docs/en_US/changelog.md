### 05/22/2019

-   First beta version 

### 05/24/2019

-   Bugfix for the Fix position command for pets

### 05/29/2019

-   Widget for pets with image
-   First stable version

### 12/06/2019

-   Changement des commandes pour la position des animaux, elles sont maintenant binaires
avec 0 = extérieur et 1 = intérieur.
-   Nouveau widget pour les animaux.
-   Les photos des animaux sont maintenant téléchargés lors d'une synchro et stockées en local
dans le répertoire /data/.
-   Après l'exécution d'une commande action, la commande info correspondante est mise à jour et le
widget est actualisé.

### 13/08/2019

- Compatibilité php 7.3 et font awesome 5
- Version minimum de Jeedom requise : 3.3

### 28/08/2019

- Commandes action pour autoriser ou interdire de sortie un animal sur une chatière
- Lien avec le plugin Agenda
- Nouvelle commande info donnant la date et l'heure de la dernière entrée/sortie d'un animal

### 15/10/2019

- Nouvelle commande info donnant le nom de la chatière par laquelle a eu lieu la dernière entrée/sortie d'un animal
- Premières commandes pour un distributeur de nourriture (l'intégration du distributeur dans le plugin n'est pas complète)

### 04/11/2019

- Tag "v4" pour le market

### 13/11/2019

- Le logicalId de toutes les commandes info des équipements a changé pour inclure si c'est une info de type status ou control. 
C'était une erreur de ma part de ne pas l'avoir inclus au départ, mieux vaut le corriger maintenant.
- Nouvelles commandes relatives au distributeur de nourriture pour les animaux **Dernier repas**, **Mangé dans**, **Poids bol 1**,
**Poids bol 2**
- Maintenant les date/heure pour les entrées/sorties et les repas sont prises sur le serveur surepetcare.io pluôt que l'horloge de Jeedom
cela est important si vous choisissez un intervalle de mise à jour long pour que ces événements soient correctement datés.
- La documentation a été mise à jour et les images refaites pour tenir compte de l'évolution du plugin
- Plus d'infos ont été ajoutées aux objets : version, date de création, date de mise à jour, sexe pour les animaux et addresse MAC pour les équipements.
- Diminution du nombre de requêtes au serveur lors de la synchronisation et lors du cron
- L'intervalle auquel le cron appelle le serveur est maintenant configurable 
- nouvelles commandes permettent de consulter et de modifier les heurs de début et de fin du couvre-feu. Attention de bien lire la documentation 
pour utiliser ces commandes.
- les deux commandes **Autoriser animal**et**Interdire animal** ont été supprimées pour la grande chatière car comme elle n'a qu'un capteur
elle ne permet pas de contrôler les sorties. Ces deux commandes restent pour la petite chatière qui a 2 capteurs.
- Le bouton "+" a été supprimé de la page des objets car il n'est pas possible d'ajouter un objet manuellement il faut utiliser la synchronisation
- meilleur arrondi de certaines valeurs numériques
