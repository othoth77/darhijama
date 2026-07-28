# DATABASE.md — Notre Jour

État : schéma **implémenté en Phase 1** (migrations réelles dans les modules Templates/Orders/
Invitations/Media). Décisions et justifications détaillées : `PHASE_1.md`. Les tables socle Laravel
(`users`, `sessions`, `cache`, `jobs`, `failed_jobs`) restent créées par les migrations racine (Phase 0).

## Tables MVP (actives)

### users
Opérateurs internes uniquement (aucun compte client). Rôles gérés par `spatie/laravel-permission`
(`roles`, `model_has_roles`, `permissions`, publiées par le package).

### clients (module Orders)
| Champ | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | varchar | |
| whatsapp_phone | varchar | valeur brute saisie par l'opérateur |
| whatsapp_phone_normalized | varchar unique indexé | calculée par `ClientObserver::saving()`, clé de déduplication |
| notes | text nullable | |
| timestamps | | |

### template_categories (module Templates)
| Champ | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | varchar | ex. Classique, Moderne, Floral |
| slug | varchar unique | |
| order | int | ordre d'affichage |

### templates (module Templates)
| Champ | Type | Notes |
|---|---|---|
| id | bigint PK | |
| template_category_id | FK `restrictOnDelete` | |
| name | varchar | |
| slug | varchar unique | |
| description | text nullable | |
| preview_image_path | varchar nullable | chemin sur disque S3 |
| demo_data | json nullable | données factices page démo |
| is_active | boolean | visible dans la galerie |
| order | int | |
| timestamps | | |

### orders (module Orders)
| Champ | Type | Notes |
|---|---|---|
| id | bigint PK | |
| reference | varchar unique | `NJ-{annee}-{sequence}`, génération race-safe (`CreateOrderService`) |
| client_id | FK `clients` `restrictOnDelete` | |
| template_id | FK `templates` nullable `nullOnDelete` | modèle choisi côté commande, copié vers `invitations.template_id` à la création (copy-on-create) |
| subtotal / discount / total | decimal(10,3) | TND = 3 décimales ; `total` validé par `OrderObserver` |
| paid_amount | decimal(10,3) | |
| currency | varchar(3) default TND | jamais `DT` en base |
| payment_method | varchar nullable | |
| paid_at | timestamp nullable | |
| status | enum | nouveau, contact_whatsapp, paye, en_production, publie, livre, annule |
| notes | text nullable | |
| created_by | FK users nullable | |
| timestamps | | |

### invitations (module Invitations)
| Champ | Type | Notes |
|---|---|---|
| id | bigint PK | |
| order_id | FK `orders` indexé, **non unique**, `cascadeOnDelete` | 1 commande → N invitations |
| template_id | FK `templates` nullable `nullOnDelete` | copié depuis `order->template_id` si non fourni |
| public_token | varchar(26) unique | `Str::ulid()->toBase32()`, non énumérable, base de l'URL publique `/i/{public_token}` |
| qr_code_path | varchar nullable | chemin historique du cache QR ; recréé s'il manque, conservé lors d'une régénération et remis à `null` lors d'une invalidation |
| slug | varchar nullable | facultatif, lisible (ex. leila-karim), non utilisé seul dans l'URL |
| title | varchar nullable | |
| event_type | varchar default mariage | |
| locale | varchar(5) default fr | |
| timezone | varchar default Africa/Tunis | |
| groom_name / bride_name | varchar | |
| wedding_date | datetime | base du compte à rebours |
| venue_name | varchar nullable | |
| venue_address | varchar nullable | |
| maps_embed_url | varchar nullable | iframe Google Maps (gratuit) |
| lat / lng | decimal(10,7) nullable | optionnel |
| message | text nullable | |
| qr_code_path | varchar nullable | chemin S3 du QR (endroid/qr-code) |
| status | enum | brouillon, publie, archive |
| published_at | timestamp nullable | jamais réécrit si déjà publié (`PublishInvitationService`, idempotent) |
| timestamps | | |

### media (module Media, polymorphe)
| Champ | Type | Notes |
|---|---|---|
| id | bigint PK | |
| mediable_type / mediable_id | morph | alias stable (`template`, `invitation`) via `Relation::enforceMorphMap()`, jamais le FQCN brut |
| disk / path | varchar | |
| type | varchar | image, qr_code, video... |
| original_name / mime_type | varchar nullable | |
| size | unsigned int nullable | octets |
| order | int default 0 | |
| timestamps | | |

Suppression d'un `Template`/`Invitation` : uniquement les lignes `media` liées sont supprimées
(`TemplateObserver`/`InvitationObserver::deleting()`), jamais les fichiers physiques (`PHASE_1.md` §8).
Ce comportement historique est conservé par `MediaService::deleteFor(..., deleteFiles: false)` ; le
service central permet aussi la suppression physique explicite pour les flux qui en sont propriétaires.

### whatsapp_click_events (Dashboard Analytics)
| Champ | Type | Notes |
|---|---|---|
| id | bigint PK | |
| source | varchar(64) indexé | emplacement du CTA |
| invitation_id | bigint nullable indexé | contexte technique optionnel, sans FK inter-module |
| visitor_hash | char(64) | empreinte HMAC pseudonymisée |
| deduplication_key | char(64) unique | déduplication atomique par minute |
| metadata | json nullable | contexte explicitement autorisé uniquement |
| occurred_at | timestamp indexé | |

### page_views (Dashboard Analytics — visiteurs)
| Champ | Type | Notes |
|---|---|---|
| id | bigint PK | |
| source | varchar(64) indexé | landing, invitation ou futur module public |
| subject_type / subject_id | varchar nullable indexé | référence technique sans dépendance de modèle |
| visitor_hash | char(64) | empreinte HMAC pseudonymisée |
| deduplication_key | char(64) unique | déduplication atomique par heure |
| occurred_at | timestamp indexé | |

### analytics_events (événements métier)
| Champ | Type | Notes |
|---|---|---|
| id | bigint PK | |
| type | varchar(40) indexé | invitation_view, invitation_published, rsvp_submitted, order_created |
| subject_type / subject_id | varchar nullable indexé | référence technique réutilisable |
| source | varchar nullable indexé | source applicative optionnelle |
| visitor_hash | char(64) nullable | uniquement pour les événements visiteurs |
| deduplication_key | char(64) unique | idempotence atomique |
| metadata | json nullable | identifiants techniques minimaux uniquement |
| occurred_at | timestamp indexé | |
### audit_logs (audit administratif)
| Champ | Type | Notes |
|---|---|---|
| id | bigint PK | |
| user_id | FK users nullable | acteur ; conservé à `null` si l'utilisateur est supprimé |
| entity_type / entity_id | varchar nullable indexé | entité auditée sans dépendance inter-module |
| action | varchar(40) indexé | create, update, delete, restore, publish, archive, login, logout, permission_change, feature_flag_change |
| changes | json nullable | valeurs avant/après, secrets filtrés |
| ip_address | varchar(45) nullable | IPv4 ou IPv6 |
| user_agent | text nullable | limité à 1024 caractères par le service |
| occurred_at | timestamp indexé | |

### notifications (canal database Laravel)
Table polymorphe standard Laravel contenant les notifications internes visibles par leurs destinataires.

### notification_deliveries (orchestration)
| Champ | Type | Notes |
|---|---|---|
| recipient_type / recipient_id | varchar | destinataire Eloquent |
| idempotency_key | char(64) unique | une seule mise en file par intention |
| message | json | contenu et canaux demandés |
| status / attempts | varchar / smallint | pending, delivered ou failed ; compteur de tentatives |
| failed_at / failure_reason | nullable | diagnostic borné du dernier échec définitif |
| delivered_at | nullable | fin de livraison |
| timestamps | | |
### features (Laravel Pennant)
Table standard du package — stocke l'état réel (actif/inactif) de chaque flag déclaré dans
`config/features.php`.

## Tables des modules futurs (créées uniquement quand le module sera développé)

| Table | Module | Description |
|---|---|---|
| rsvp_responses | RSVP | invitation_id, guest_name, attending, guests_count, message |
| media_files | Media (galeries) | invitation_id, type (photo/video), path, uploaded_by |
| guestbook_entries | Guestbook | invitation_id, author_name, message, approved |
| timeline_events | Timeline | invitation_id, title, description, event_time, order |
| ai_album_jobs | AI | invitation_id, status, input_media_id, output_media_id |
| singles_profiles | Singles | **non défini** — aucune migration créée tant que le périmètre n'est pas spécifié |
| personal_access_tokens | Api | table standard Sanctum |

## Notes de sécurité

- `public_token` : `Str::ulid()->toBase32()`, 26 caractères Crockford base32, unique, indexé.
  Ne jamais exposer l'`id` auto-incrémenté d'une invitation dans une URL publique.
- `invitations.order_id` en `cascadeOnDelete` (supprimer une commande supprime ses invitations) ;
  `template_id` (sur `orders` et `invitations`) en `nullOnDelete` — un modèle supprimé ne doit pas
  supprimer les commandes/invitations déjà vendues. `orders.client_id` en `restrictOnDelete` — un
  client ayant des commandes ne peut pas être supprimé.
