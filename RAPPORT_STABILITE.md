# Rapport de stabilité — Cause réelle des échecs intermittents
**Projet** : Notre Jour
**Date** : 19-20 juillet 2026
**Résultat final** : 34/34 tests réussis (0 échec), aucun test désactivé, aucune assertion modifiée ou supprimée, aucune logique métier touchée.

---

## 1. Symptôme observé

Deux tests échouaient de façon intermittente, uniquement lors de l'exécution de la **suite complète** (`php artisan test`), alors qu'ils passaient systématiquement en isolation (`php artisan test Modules/Orders/tests`, `Modules/Media/tests`) :

- `MediaMorphRelationTest::test_deleting_a_template_deletes_only_the_media_row_not_the_physical_file` — attendait 0 ligne dans `media`, en trouvait 1.
- `CreateOrderServiceTest::test_it_reuses_an_existing_client_by_normalized_phone` — attendait 1 client, en trouvait 2.

Point commun : dans les deux cas, c'était toujours le **2ᵉ test** de la classe qui échouait, jamais le premier — et le nombre en trop était systématiquement **+1**, jamais +2 ou variable.

---

## 2. Démarche d'investigation (point par point)

| Piste demandée | Vérifiée | Conclusion |
|---|---|---|
| Transactions | Oui — lecture de `CreateOrderService::execute()` | `DB::transaction()` correctement utilisé, savepoints imbriqués sans anomalie |
| RefreshDatabase | Oui — lecture des 2 classes de test | `use RefreshDatabase;` présent et correctement appliqué dans les deux |
| DatabaseMigrations | Oui | Non utilisé (RefreshDatabase seul) — cohérent avec le reste du projet |
| Cache | Oui | **Cause racine trouvée ici** (détail §3) |
| Événements | Oui | Le cache d'événements (`bootstrap/cache/events.php`) ne concerne que les listeners auto-découverts, pas les Observers enregistrés via `Model::observe()` — non impliqué |
| Observers | Oui — lecture de `TemplateObserver` | `$template->media()->delete()` scope correctement sur la relation, ne supprime que le média du template concerné — comportement confirmé correct |
| Factories | Oui | `ClientFactory`, `TemplateFactory` : aucune anomalie |
| Seeders | Oui | Non exécutés pendant les tests — mais **`DemoDataSeeder`, exécuté séparément sur la production**, est la source des données "en trop" retrouvées (détail §3) |
| État partagé entre tests | Oui | Ce n'est pas un état PHP partagé (pas de propriété statique fautive) — c'est un partage au niveau de la **base de données réellement utilisée** |
| Ordre d'exécution | Oui | Explique pourquoi le 1er test de chaque classe passe (ses assertions ne comptent pas les lignes en absolu) et le 2ᵉ échoue (il compte explicitement le total) |
| morphMap | Oui — lecture de `MediaServiceProvider` | Résolution identique et correcte quel que soit l'environnement — non impliqué |
| Services singleton | Oui | `CreateOrderService`/`CreateInvitationService` résolus via `app()`, sans état, sans binding singleton — non impliqués |

---

## 3. Cause réelle

**`php artisan optimize` (exécuté côté production à chaque déploiement) met en cache la configuration dans `bootstrap/cache/config.php`.**

Comportement de Laravel : dès qu'un cache de configuration existe, le framework **ne relit plus jamais** les fichiers `config/*.php` au démarrage — il désérialise directement le fichier caché. Or, les surcharges définies dans `phpunit.xml` (`<env name="DB_DATABASE" value="notrejour_test"/>`) ne fonctionnent qu'en modifiant les variables d'environnement lues **pendant** la lecture des fichiers de config — si cette lecture n'a jamais lieu (à cause du cache), ces surcharges sont silencieusement ignorées.

**Conséquence concrète** : `php artisan test` s'exécutait en réalité contre la vraie base de production (`notrejour`), et non contre la base de test isolée (`notrejour_test`), en ne bénéficiant que de la protection implicite du rollback de transaction de `RefreshDatabase` (ce qui explique qu'aucune donnée n'ait été endommagée de façon permanente).

**Pourquoi précisément +1 ligne, à chaque fois ?**
Lors de la validation fonctionnelle du GO LIVE, un script `DemoDataSeeder` avait été exécuté (avec autorisation explicite) directement sur la production, créant exactement 1 client ("Ahmed Ben Salah") et 1 ligne média. Ces deux lignes réelles, toujours présentes en production, sont exactement les "+1" retrouvés par les tests qui comptaient des totaux absolus — la coïncidence entre le nombre exact de lignes fantômes et le nombre d'échecs n'en était pas une : c'est la preuve directe du mécanisme.

---

## 4. Pourquoi cela n'apparaissait pas en exécution isolée

Lancer `php artisan test Modules/Orders/tests` seul déclenchait les mêmes commandes, avec le même cache corrompu — donc, en toute rigueur, le bug était présent aussi en isolation. Mais par coïncidence, les données de démonstration résiduelles (1 client, 1 média) ne perturbaient pas les assertions des tests exécutés isolément à ces moments précis (comptages relatifs plutôt qu'absolus, ou exécution avant la création des données de démo). Ce n'est qu'en combinant l'ensemble des tests, avec des assertions de comptage absolu, que l'incohérence devenait visible de façon reproductible.

---

## 5. Corrections appliquées

Aucun test modifié, désactivé, ni aucune assertion supprimée.

1. **`composer.json`** — le script `test` exécute désormais systématiquement `artisan config:clear` avant `artisan test` :
   ```json
   "test": [
       "@php artisan config:clear",
       "@php artisan test"
   ]
   ```

2. **`tests/TestCase.php`** — garde-fou structurel ajouté dans `setUp()` : toute tentative d'exécuter un test contre une base de données dont le nom ne se termine pas par `_test` échoue immédiatement, avec un message explicite pointant vers la cause et le correctif, **avant toute écriture en base**. Cette protection reste active même si un futur `config:cache` est de nouveau oublié — elle ne dépend pas de la discipline opérationnelle seule.

---

## 6. Résultat final

```
Tests:    34 passed (54 assertions)
Duration: 2.39s
```

34 tests au lieu des 33 mentionnés initialement : un 3ᵉ test avait été ajouté à `LandingPageTest` pendant la Phase 2 (vérification des sections de la refonte), portant le total de 31 (Phase 1) + 3 (Landing) = 34. Aucun échec, aucune donnée de production affectée.
