# Plugin Comptage

Ce plugin permet de transformer des données en provenance d'un compteur en donées énergetiques. 

Il gère le gaz, l'électricité et l'eau.

> **IMPORTANT**
>
> N'oubliez pas d'activer les panels (desktop et mobile) du plugin dans la configuration de celui-ci.

# Configuration

La configuration du plugin nécessite une commande represantant l'evolution de votre compteur. Le plugin gére aussi bien les compteur totalisant ou compteur impulsionnel(dans ce cas le plugin incrémente l'index automatiquement).

# Equipements

Vous pouvez faire autant d'équipements de comptage que vous voulez :

* **Type** :  Eau, Gaz ou Electricité.

* **Type Comptage** :	Total( lorsque votre commande represent un index, type Linky, Gaspar...)
						Impulsion( lorsque votre commande represent une impulsion) Dans ce cas le plugin augmente l'index de +1 à chaque changement de la commande d'origine, pour vue que sa valeur soit différente de 0.
* **Compteur impulsions** : commande info donnant l'information de comptage.
* **Type impulsion** : chisissez si le comtage détermine des Watts ou des litres (non utilisé pour l'instant).

* **Poid par impulsion** : ce que represente une impulsion en W/kW ou l/m3* et le choix de son unité.
							ex: 10 et unité (l) veut dire qu'une impulsion represente 10 litre.
* **Coef de conversion** : Pour les équipements gaz c'est le coef d'équivalence 1m3 gaz vs 1kWh, si vous ne le conaissez pas mettez 10.91, pour les autres équipement mettre à 1. ce champ peut prendre une commande info si vous disposer de cette info dans un autre plugin...

Une foie l'équipement renséigné et sauvegardé vous aurez des bouttons pour une configuration avancé;
* **Corriger Vol_index** : permet de renseigner manuellement une valeur additionnelle pour que l'index de comptage de l'équipement corresponde à l'index réelle de votre compteur. Il Important de saisir cette valeur le plus tôt possible pour avoir un historique cohérant.

* **Renseigner des valeurs** : permet de renseigner manuellement sa consommation sur une période.