Description
===

Plugin permettant de les ojets connectés pour animaux de la marque Sure Petcare (anciennement Sureflap).

Pour le moment les seuls objets connectés sont des chatières
- La grande chatière connect (Pet Porte Connect en anglais)
- La chatière à puce électronique connect (Microchip Cat Flap Connect en anglais)

Note : le plugin ne communique pas directement avec la chatière ou le hub,
il interroge le serveur surepetcare.io qui lui communique avec le hub et à travers lui avec les objets connectés.
A ma connaissance personne n'a pu decoder le protocole utilisé lors des communications chatière <-> hub 
ou hub <-> serveur surepetcare.io ce qui s'explique car ces communications sont sécurisées.

Avant d'activer le plugin il faut que votre compte sur le serveur de surepetcare.io soit créé avec un mot de passe
et il faut que l'appli IOS ou Android fonctionne.

Configuration du plugin
===

Il faut entrer :

-   **Adresse mail** : L'adresse mail que vous avez donnée lors de la création du compte sur le site surepetcare.io ou dans l'app IOS ou Android

-   **Mot de passe** : Le mot de passe que vous avez choisi lors de la création du compte sur le serveur de Bosch.

Et ne pas oublier de cliquer sur **Sauvegarder**.

Création des équipement
===


Les commandes disponibles
===

En cliquant sur l'onglet commande vous accédez aux commandes disponibles.

Ces commandes sont différentes pour un hub, un objet connecté ou un animal.

Changer certains noms de commandes peut provoquer des dysfonctionnements.

Commande pour un hub

| Nom                                  | Type    | Sous type  | Rôle                                                                                                                                                               |
| :--:                                 | :---:   | :---:      | :---:                                                                                                                                                              |
| **Brillance**                        | action  | numeric    | Fixe le mode d'allumage des leds ("oreilles") du hub : vif, atténué ou aucun.                                                                                   |                                                                                         |

Commandes pour une chatière

| Nom                                  | Type    | Sous type  | Rôle                                                                                                                                                               |
| :--:                                 | :---:   | :---:      | :---:                                                                                                                                                              |
| **Verrouillage**                     | action  | numeric    | Fixe le mode de verrouillage de la chatière                                                                                                                        |

Commandes pour un animal

| Nom                                  | Type    | Sous type  | Rôle                                                                                                                                                               |
| :--:                                 | :---:   | :---:      | :---:                                                                                                                                                              |
| **Localisation**                     | info    | numeric    | Indique si l'animal est à l'extérieur ou à l'intérieur                                                                                                             |

FAQ
===
