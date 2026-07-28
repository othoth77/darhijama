# Feuille de route d’implémentation production — Notre Jour

## 1. Objectif et principes d’exécution

Cette feuille de route décrit le chemin de mise en production à partir de l’état réel du dépôt.
Elle conserve l’architecture Laravel modulaire existante, le back-office Filament, les Feature Flags
Pennant et le parcours commercial centré sur WhatsApp.

Ordre directeur :

1. stabiliser l’existant et le déploiement ;
2. consolider les composants partagés réutilisables ;
3. terminer les modules métier core ;
4. ajouter l’observabilité, la sécurité et l’exploitation ;
5. activer les modules futurs un par un, uniquement après validation produit.

Chaque phase doit être livrée séparément, testée, documentée et validée avant le démarrage de la
suivante.

## 2. État actuel

### 2.1 Socle partagé

| Composant | État | Observations |
|---|---|---|
| Laravel 12 / PHP 8.3 | Implémenté | Bootstrap Laravel 12 opérationnel. |
| Modules nwidart | Implémenté | Treize modules déclarés ; six core actifs et sept futurs désactivés structurellement. |
| Feature Flags Pennant | Partiel | Registre et définitions présents ; aucune interface d’administration. |
| Authentification admin | Implémenté | Accès Filament réservé au rôle `admin`. |
| Autorisations Spatie | Implémenté | Permissions core et Policies métier présentes. |
| Filament | Implémenté | Découverte générique des Resources de modules actifs. |
| WhatsApp | Implémenté | Configuration centralisée, builder et helper partagés. |
| Stockage S3-compatible | Partiel | Disque configuré ; politique public/signé non arrêtée et cycle de vie incomplet. |
| Front-end | Implémenté | Blade, Tailwind CSS v4, Alpine.js et Vite. |
| CI | Partiel | Validation Composer, Pint, modules, build et tests ; analyse statique, audits et déploiement absents. |
| Tests | Partiel | Couverture métier significative ; validation locale actuellement dépendante de MySQL/PDO et du build Vite. |
| API publique | Non implémenté | Module structurel désactivé. |
| Observabilité | Non implémenté | Pas de journalisation métier structurée, métriques, alertes ni suivi d’erreurs documenté. |

### 2.2 Modules core

| Module | État | Réalisé | Manquant pour la production |
|---|---|---|---|
| Landing | Partiel | Landing responsive, contenu commercial, CTA WhatsApp, tests Feature. | Brancher la galerie réelle, SEO complet, analytics, pages légales, liens sociaux définitifs et audit accessibilité/performance. |
| Templates | Partiel | Modèles, catégories, migrations, factories, seeder, Policies et CRUD Filament. | Galerie publique dynamique, démonstrations réelles, service média partagé, validation/optimisation des images et tests de parcours public. |
| Orders | Avancé | Clients, commandes, statuts, normalisation téléphone, calculs, création transactionnelle, CRUD Filament et tests. | Durcir la génération de référence à forte concurrence, compléter le workflow paiement/livraison, historique/audit des changements et exports opérationnels. |
| Invitations | Avancé | Création, publication, token public, page publique, programme, galerie, vidéo, audio, Maps, compte à rebours, QR, RSVP, Filament et tests. | Rate limiting/anti-spam RSVP, validation des URLs externes, gestion média unifiée, prévisualisation avant publication, cache/invalidation et validation production complète. |
| Media | Partiel | Modèle polymorphe, migration, morph map, Policy, factory et Resource Filament. | Service central d’upload/suppression, optimisation Intervention Image, règles MIME/taille, noms sûrs, transactions compensatoires, URLs signées éventuelles et nettoyage des fichiers orphelins. |
| Admin | Partiel | Panel Filament central et contrôle d’accès. | Dashboard métier, analytics, Feature Flags, journal d’audit, gestion opérateurs/permissions, supervision des erreurs et procédures d’exploitation. |

### 2.3 Modules futurs

| Module | État | Condition d’implémentation |
|---|---|---|
| RSVP | Coquille désactivée | Clarifier son rôle, car un RSVP MVP existe déjà dans `Invitations`; éviter toute duplication de modèle ou de workflow. |
| Guestbook | Coquille désactivée | Spécification produit, modération, anti-spam et politique de rétention. |
| Timeline | Coquille désactivée | Spécification produit et réutilisation des composants média/ordre. |
| Notifications | Coquille désactivée | Choix des canaux et fournisseurs, consentement, files d’attente, retries et suivi de livraison. |
| AI | Coquille désactivée | Cas d’usage validé, fournisseur, coûts, sécurité des médias et consentement. |
| Singles | Coquille désactivée | Périmètre fonctionnel explicitement requis avant tout développement. |
| Api | Coquille désactivée | Contrats d’API, authentification Sanctum, quotas, versionnement et documentation. |

## 3. Dette technique et risques

### Priorité critique

- La Phase 3 n’est pas clôturée côté exploitation : le déploiement VPS et la suite complète doivent
  confirmer l’autoload sensible à la casse de `Modules/Invitations/Database/Factories`.
- La validation locale n’est pas reproductible sans `pdo_mysql`, une base `*_test` et un manifest
  Vite ; l’environnement de développement doit être rendu déterministe.
- Les documents historiques `ROADMAP.md` et `TODO.md` ne reflètent plus exactement le code livré.
- Le contrôleur public d’invitation contient la génération et le stockage du QR Code, alors que cette
  responsabilité est réutilisable et relève d’un service d’infrastructure.
- Les uploads Filament ne passent pas encore par un service Media unique malgré la convention
  d’architecture annoncée.

### Priorité haute

- Les dépendances inter-modules sont directes dans plusieurs modèles, Resources et services
  (`Templates`, `Orders`, `Invitations`, `Media`) alors que la documentation privilégie des contrats
  partagés ou des événements. La production doit éviter d’étendre ce couplage.
- La couche Repository documentée n’est pas appliquée au code métier actuel. Ne pas lancer une
  réécriture globale : introduire des contrats uniquement aux nouvelles frontières partagées ou
  lorsque les tests justifient l’abstraction.
- Le tracking WhatsApp Alpine envoie vers un endpoint non implémenté.
- Les tables analytics annoncées dans la documentation ne sont pas présentes.
- Le contenu de galerie de la Landing reste statique malgré l’existence du catalogue Templates.
- La suppression des lignes Media ne supprime volontairement pas les fichiers physiques, ce qui
  crée un risque d’objets orphelins et de coûts de stockage.
- La politique S3 public versus URL signée n’est pas tranchée.
- Le RSVP public n’a pas de limitation de débit, mécanisme anti-robot ni règle de déduplication.

### Priorité moyenne

- Le module Admin est essentiellement structurel ; les fonctions d’exploitation annoncées sont
  absentes.
- La CI ne contient ni Larastan/PHPStan, ni audit Composer/NPM, ni test de déploiement.
- Aucun journal d’audit métier n’enregistre publication, changement de statut ou action opérateur.
- Les pages légales, la politique de confidentialité et les liens sociaux définitifs manquent.
- La charte graphique est encore signalée comme provisoire.
- La politique de rétention des données analytics et RSVP n’est pas définie.

## 4. Phases d’implémentation

## Phase 0 — Clôture et reproductibilité de l’existant

### État au 2026-07-28

- Validation locale terminée : configuration Laravel complète, migration Spatie restaurée, factories,
  seeders et migrations vérifiés sur base isolée.
- Composer validé, autoload optimisé sans alerte PSR-4, 56 tests / 96 assertions, Pint,
  Vite et les caches Laravel validés.
- Compatibilité de casse Linux corrigée pour Database/Factories, Seeders, Migrations et Tests.
- Pipeline MySQL/Linux et validation VPS restent les deux contrôles externes avant clôture définitive.

### Objectif

Obtenir un état de référence vert, reproductible localement, en CI et sur le VPS avant toute nouvelle
fonctionnalité.

### Travaux

1. Vérifier l’autoload PSR-4 et la casse de tous les dossiers `Database/Factories`, migrations et
   seeders sur Linux.
2. Exécuter `composer dump-autoload -o`, le build Vite, les migrations et la suite complète sur un
   environnement MySQL de test.
3. Documenter les prérequis locaux : extensions PHP, base `notrejour_test`, Node et Composer.
4. Corriger la cohérence du nom de base CI (`notre_jour_test` versus `notrejour_test`).
5. Clôturer la validation Phase 3 et mettre à jour les documents historiques sans modifier le
   comportement métier.
6. Établir une sauvegarde et une procédure de rollback VPS.

### Critères de sortie

- Tous les tests passent localement, en CI et sur le VPS.
- Le build de production est généré sans erreur.
- Les migrations montantes et descendantes sont vérifiées sur une base éphémère.
- Aucun écart de casse n’existe entre Windows et Linux.
- La procédure de déploiement et de rollback est exécutable par un second opérateur.

## Phase 1 — Fondations partagées réutilisables

### Objectif

Créer les briques transverses nécessaires aux modules core et futurs avant d’étendre les workflows
métier.

### 1.1 Media

**État : terminé le 2026-07-28.** Service partagé, profils de validation, nommage ULID, stockage
local/S3-compatible, suppression, URL, génération de contenu et intégration des consommateurs existants
validés par la suite complète.

- Créer un service unique d’upload, validation, optimisation, stockage, URL et suppression.
- Définir des profils réutilisables : aperçu Template, galerie Invitation, QR, audio et vidéo.
- Centraliser les règles de taille, MIME, dimensions, nommage et visibilité.
- Ajouter un nettoyage sûr des objets orphelins et des tests avec `Storage::fake()`.
- Décider et documenter la stratégie S3 publique ou signée.

### 1.2 QR et liens publics

**État : terminé le 2026-07-28.** Génération QR partagée et idempotente, URLs publiques par token, cache fichier, régénération atomique, invalidation, nettoyage et intégration Invitation validés sur disques locaux et S3-compatibles.

- Extraire la génération QR du contrôleur vers un service partagé idempotent.
- Centraliser les URL publiques, le cache, l’invalidation et le choix du disque.
- Conserver le token opaque existant et tester les erreurs de stockage.

### 1.3 Analytics et événements

**État : terminé le 2026-07-28.** Événements partagés, listeners, stockage analytics spécialisé et générique, endpoint WhatsApp limité, déduplication et minimisation des données validés.

- Définir des événements applicatifs partagés : clic WhatsApp, vue publique, commande créée,
  invitation publiée et RSVP soumis.
- Implémenter les tables `whatsapp_click_events` et `page_views` avec données minimales.
- Ajouter l’endpoint de tracking annoncé, validation, limitation de débit et politique de rétention.
- Décorréler la collecte des modules métier au moyen de listeners.

### 1.4 Audit et exploitation

**État : terminé le 2026-07-28.** Audit transverse des actions administratives et fondation de notifications événementielles, idempotentes, réessayables et extensibles validés sans fournisseur externe actif.

- Ajouter un journal d’audit pour les actions admin sensibles.
- Standardiser la journalisation avec identifiant de corrélation et contexte minimal sans données
  personnelles inutiles.
- Préparer une abstraction de notifications asynchrones avec files, retries et échec définitif, sans
  activer de fournisseur.

### 1.5 Composants UI

**État : terminé le 2026-07-28.** Composants Blade et fabriques Filament partagés, consommateurs existants refactorés, rendu responsive, actions critiques et build de production validés.

- Extraire les composants Blade réutilisables : CTA WhatsApp, média responsive, partage, états vides,
  erreurs de formulaire et métadonnées SEO.
- Définir les composants Filament communs : statuts, uploads Media, actions publier/archiver et
  confirmations.

### Critères de sortie

- Aucun nouvel upload ne contourne le service Media.
- QR, tracking, audit et CTA disposent de tests unitaires/Feature.
- Les composants partagés sont utilisés par au moins deux consommateurs ou répondent à une frontière
  d’infrastructure stable.
- Aucune régression sur les parcours existants.

## Phase 2 — Finalisation des modules core publics

### 2.1 Templates et Landing

**État : terminé le 2026-07-28.** Catalogue dynamique, détails/démos, filtres, recherche, pagination, Media partagé, SEO technique, pages légales, analytics, accessibilité et responsive validés.

1. Exposer un contrat de lecture de catalogue sans importer les composants internes de Templates dans
   Landing.
2. Remplacer la galerie statique par les Templates actifs ordonnés.
3. Ajouter les pages de démonstration fondées sur `demo_data`.
4. Utiliser les profils Media partagés pour aperçus et galeries.
5. Ajouter SEO, Open Graph, sitemap, robots, canonical et données structurées.
6. Finaliser accessibilité, responsive, performance et pages légales.

### 2.2 Invitations publiques

1. Ajouter une prévisualisation admin sécurisée des brouillons.
2. Utiliser les services Media et QR partagés.
3. Valider strictement Maps, vidéo, audio et autres URLs externes.
4. Ajouter rate limiting et protection anti-spam au RSVP.
5. Définir la stratégie de correction/déduplication des réponses RSVP.
6. Tester cache, invalidation, erreurs de média et parcours mobile complet.

### Critères de sortie

- Le catalogue public reflète les données administrées.
- Une invitation peut être prévisualisée, publiée, partagée et consultée sur mobile.
- Les médias sont optimisés, sécurisés et nettoyables.
- Les objectifs Core Web Vitals, SEO et accessibilité sont mesurés et acceptés.

## Phase 3 — Finalisation des opérations métier

### 3.1 Orders

- Définir explicitement les transitions de statut autorisées.
- Ajouter un historique de statut et des actions Filament contrôlées.
- Durcir la génération de référence avec une séquence robuste en concurrence.
- Finaliser les champs paiement manuel, livraison et notes opérateur.
- Ajouter filtres, recherche, export CSV et indicateurs opérationnels.

### 3.2 Invitations dans le back-office

- Compléter l’édition du programme, des médias et des informations publiques.
- Ajouter actions publier, archiver, dupliquer et copier le lien.
- Afficher QR, URL publique, date de publication et réponses RSVP.
- Empêcher les états incohérents par validation métier centralisée.

### 3.3 Admin

- Construire le dashboard : commandes, conversions WhatsApp, invitations publiées, vues et RSVP.
- Ajouter la gestion des Feature Flags avec garde-fous et audit.
- Ajouter la gestion des opérateurs, rôles et permissions selon besoin validé.
- Ajouter écrans d’audit, échecs de jobs et santé applicative.

### Critères de sortie

- Le cycle commande → invitation → publication → livraison est entièrement exploitable depuis
  Filament.
- Toute transition sensible est autorisée, auditée et testée.
- Le dashboard utilise les événements et tables analytics partagés.

## Phase 4 — Sécurité, conformité et fiabilité

### Travaux

1. Audit des autorisations, CSRF, XSS, uploads, URLs externes et exposition des données.
2. Rate limiting des routes publiques et définition des seuils.
3. Politique de rétention pour analytics, RSVP, logs et médias.
4. Sauvegardes automatiques de la base et inventaire des objets S3.
5. Tests de restauration, rollback et reprise après échec.
6. Gestion des secrets hors dépôt et rotation documentée.
7. En-têtes de sécurité, CSP compatible avec les médias/Maps et cookies sécurisés.
8. Pages confidentialité, mentions légales et consentements nécessaires.

### Critères de sortie

- Aucun finding critique ou élevé non accepté.
- Une restauration base + médias est testée.
- Les politiques de conservation et suppression sont applicables.
- Les routes publiques résistent aux abus courants.

## Phase 5 — Qualité, observabilité et livraison continue

### Travaux

1. Ajouter Larastan/PHPStan avec niveau progressif et baseline contrôlée.
2. Ajouter audits Composer et NPM, vérification des licences et dépendances obsolètes.
3. Ajouter tests de fumée post-déploiement et contrôle des migrations.
4. Mettre en place suivi d’erreurs, logs centralisés, métriques et alertes.
5. Définir SLO : disponibilité, taux d’erreur, latence et succès des jobs.
6. Automatiser un déploiement atomique avec maintenance minimale et rollback.
7. Exécuter tests de charge ciblés : landing, invitation publique, QR et RSVP.

### Critères de sortie

- La branche principale ne peut pas être livrée si qualité, build ou tests échouent.
- Le déploiement est traçable, répétable et réversible.
- Les erreurs production déclenchent une alerte exploitable.
- Les seuils de performance sont respectés à la charge attendue.

## Phase 6 — Activation progressive des modules futurs

Chaque module suit le même cycle : spécification validée, schéma, service métier, Policy, interface
admin, interface publique éventuelle, tests, flag Pennant, observabilité et déploiement progressif.

Ordre recommandé :

1. **Notifications** : s’appuyer sur l’abstraction de files et d’événements, d’abord pour les usages
   internes, puis pour les canaux externes validés.
2. **Guestbook** : réutiliser Media, modération, anti-spam et rétention.
3. **Timeline** : réutiliser Media, composants ordonnables et vues Invitation.
4. **RSVP** : uniquement si le besoin dépasse le RSVP déjà intégré à Invitations ; migrer sans
   dupliquer les données ou routes existantes.
5. **Api** : exposer des contrats métier stabilisés, versionnés et protégés par Sanctum/quota.
6. **AI** : après validation juridique, sécurité média, coût et consentement.
7. **Singles** : dernier, et seulement après une spécification fonctionnelle complète.

## 5. Ordre de dépendance

```text
Validation reproductible
    └── Media partagé
        ├── Templates publics
        ├── Invitations publiques
        ├── Guestbook
        ├── Timeline
        └── AI
    └── Événements + Analytics + Audit
        ├── Dashboard Admin
        ├── Notifications
        └── API
    └── Composants UI partagés
        ├── Landing
        ├── Templates
        └── Invitations
    └── Workflows Orders/Invitations stabilisés
        ├── Notifications
        ├── API
        └── modules futurs
```

## 6. Règles de livraison

- Une phase ne mélange pas refonte d’architecture et ajout fonctionnel.
- Les migrations sont additives et réversibles sauf décision explicitement validée.
- Toute nouvelle route publique possède validation, rate limiting, tests et journalisation adaptée.
- Toute action admin sensible possède Policy, confirmation, audit et test.
- Toute intégration externe possède timeout, retry, idempotence et comportement de repli.
- Les Feature Flags ne remplacent ni les autorisations ni les statuts structurels des modules.
- Les modules futurs restent désactivés jusqu’à validation produit et opérationnelle.
- Les composants partagés ne sont extraits que pour une frontière stable ou une réutilisation réelle.
- Les documents vivants sont mis à jour dans le même lot que les changements correspondants.

## 7. Prochaine action

Exécuter le pipeline MySQL/Linux puis le protocole VPS avec les corrections Phase 0. Après validation
de ces deux environnements, démarrer la Phase 1 par le service Media partagé. Aucun nouveau module
métier ne doit être développé avant cette clôture.
