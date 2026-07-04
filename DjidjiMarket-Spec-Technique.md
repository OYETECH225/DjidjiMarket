# DjidjiMarket — Spécification technique

**Version 1.1 — Document de référence pour développement avec Claude Code**

*v1.1 : ajout des sections 8-11 (identité de marque, sécurité API, stratégie de tests, état d'avancement Phase 1) reflétant les décisions prises lors de l'implémentation. Sections 1-7 inchangées.*

---

## 1. Vision produit

DjidjiMarket est une marketplace multi-vertical pour la Côte d'Ivoire, connectant :
- des **boutiques physiques** (téléphones, électronique, etc.) qui manquent de visibilité en ligne
- des **vendeuses de nourriture** actuellement actives sur Facebook/TikTok Live, sans outil de commande fiable
- des **restaurants**
- des **livreurs** indépendants, dispatchés en temps quasi-réel

**Proposition de valeur centrale :** la confiance. Les clients ont peur de se rendre physiquement dans certains marchés (vol, insécurité) et se font arnaquer lors des ventes via TikTok Live (paiement sans réception de la commande). DjidjiMarket répond aux deux problèmes via :
1. La livraison sécurisée (évite le déplacement physique)
2. Le paiement séquestre / escrow (élimine le risque d'arnaque "j'ai payé, rien reçu")
3. La vérification progressive des vendeurs (RCCM/DFE, avec accompagnement à l'obtention)

**Nom retenu :** DjidjiMarket ("djidji" = "vrai" en nouchi ivoirien — positionnement : le vrai marché, la vraie confiance).

---

## 2. Stack technique

| Composant | Choix | Justification |
|---|---|---|
| Backend / API | Laravel (PHP) | Écosystème mature, Sanctum pour l'auth API |
| Admin & back-office vendeur | Filament (sur Laravel) | Panneau CRUD généré rapidement, gagne un temps considérable |
| Base de données | MySQL ou PostgreSQL | PostgreSQL recommandé si usage de types avancés (JSON, geo) |
| App mobile | Flutter, développée en parallèle du web dès la Phase 1 (le développement étant assuré par Claude Code, le temps de code n'est plus le facteur limitant — voir note en fin de tableau) | Un seul code pour iOS/Android, bonnes perfs sur téléphones d'entrée de gamme |
| Web | PWA légère (installable, sans Play Store), déployée en premier car sans délai de publication | Faible bande passante des utilisateurs cibles, mise en ligne immédiate pour tester avec les premiers vendeurs |
| Paiement | Agrégateur mobile money (CinetPay ou PayDunya) | Couvre Orange Money, MTN Money, Moov Money, Wave en une seule intégration |
| Notifications | WhatsApp Business API (principal) + SMS (secours) + Push (app) | WhatsApp est le canal le plus fiable et familier en Côte d'Ivoire |
| Cartographie | Mapbox ou OpenStreetMap/Leaflet | Moins coûteux que Google Maps au volume |
| File d'attente / jobs | Laravel Queues (Redis) | Nécessaire pour le dispatch livreur et les notifications asynchrones |
| Verrouillage concurrentiel | Redis lock ou transaction DB | Indispensable pour l'attribution de commande au "premier livreur qui accepte" |

**Note sur PWA vs app native :** le développement étant assuré par Claude Code, le temps de code n'est plus le facteur limitant entre les deux. Les comptes développeur Apple et Google sont déjà en place, ce qui supprime le principal délai administratif. Il reste le délai de review d'Apple (plusieurs jours, avec risque de rejet/ajustements demandés) à anticiper avant une date de lancement communiquée publiquement. La PWA reste déployée en premier car elle est en ligne immédiatement sans aucun délai de publication.

---

## 3. Modèle de données (entités principales)

### 3.1 Utilisateurs et rôles

```
users
- id
- name
- phone (unique, identifiant principal — pas forcément d'email)
- email (nullable)
- password
- role: enum [client, vendor, courier, admin, partner_manager]
- created_at, updated_at
```

### 3.2 Vendeurs

```
vendors
- id
- user_id (FK users)
- business_name
- vendor_type: enum [boutique, street_food, restaurant]
- slug (utilisé pour le lien perso : djidjimarket.ci/boutique/{slug})
- description
- logo_url, cover_url
- address_text
- latitude, longitude
- verification_level: enum [non_verifie, identite_confirmee, verifie]
- rccm_number (nullable)
- dfe_number (nullable)
- rccm_document_url (nullable)
- cni_document_url
- rccm_assist_status: enum [null, dossier_recu, depose_cepici, en_attente, obtenu]
- commission_rate (decimal, par défaut selon vertical)
- is_active (boolean)
- created_at, updated_at
```

### 3.3 Catalogue (listings polymorphes)

```
listings
- id
- vendor_id (FK vendors)
- type: enum [produit, plat_du_jour, menu_item]
- name
- description
- price
- currency (par défaut XOF)
- stock_quantity (nullable — non pertinent pour plat_du_jour)
- available_from, available_until (nullable — utile pour plat_du_jour/street_food)
- photo_urls (JSON array)
- display_number (entier — pour les commandes vocales en TikTok Live, ex: "l'article numéro 3")
- promo_code (nullable, propre au vendeur)
- is_active
- created_at, updated_at
```

### 3.4 Commandes

```
orders
- id
- client_id (FK users)
- vendor_id (FK vendors)
- courier_id (FK users, nullable tant que non assigné)
- status: enum [en_attente_paiement, paiement_sequestre, confirmee, en_preparation,
                cherche_livreur, livreur_assigne, recuperee, en_livraison,
                livree, paiement_libere, litige_ouvert, annulee]
- delivery_latitude, delivery_longitude
- delivery_address_text
- total_amount
- delivery_fee
- commission_amount
- source: enum [app, web, tiktok_live, lien_vendeur] (pour tracking de la conversion)
- promo_code_used (nullable)
- created_at, updated_at

order_items
- id
- order_id (FK orders)
- listing_id (FK listings)
- quantity
- unit_price

order_status_history
- id
- order_id
- status
- changed_at
- changed_by (user_id ou "system")
```

### 3.5 Paiement / séquestre

```
payments
- id
- order_id (FK orders)
- provider: enum [orange_money, mtn_money, moov_money, wave, cash_on_delivery]
- provider_transaction_id
- amount
- status: enum [initie, confirme, sequestre, libere, rembourse, echoue]
- escrow_released_at (nullable)
- created_at, updated_at

payouts
- id
- vendor_id (FK vendors)
- amount
- period_start, period_end
- status: enum [en_attente, envoye, echoue]
- payout_method (mobile_money)
- created_at
```

### 3.6 Litiges

```
disputes
- id
- order_id (FK orders)
- opened_by (user_id)
- reason
- status: enum [ouvert, en_cours, resolu_client, resolu_vendeur, clos]
- resolution_notes
- created_at, resolved_at
```

### 3.7 Livraison

```
couriers (extension de users avec role=courier)
- id
- user_id (FK users)
- vehicle_type: enum [moto, tricycle, velo, pied]
- cni_document_url
- vehicle_registration_url
- verification_status: enum [en_attente, verifie, rejete]
- current_latitude, current_longitude (mis à jour à chaque statut, pas en continu)
- is_available (boolean)
- rating_average
- created_at, updated_at

delivery_assignments
- id
- order_id (FK orders)
- courier_id (nullable tant que non accepté)
- offered_to (JSON array de courier_ids notifiés)
- accepted_at
- expanded_radius_at (timestamp — pour tracer l'élargissement automatique du rayon)
- status: enum [en_recherche, accepte, recupere, livre, expire]
```

### 3.8 Avis et notation

```
reviews
- id
- order_id (FK orders)
- reviewer_id (FK users)
- reviewee_type: enum [vendor, courier]
- reviewee_id
- rating (1-5)
- comment
- created_at
```

### 3.9 Partenaires et leads

```
partners
- id
- name
- partner_type: enum [banque, assurance, gestion_patrimoine, annonceur]
- contact_email
- lead_delivery_method: enum [email, webhook, dashboard]
- webhook_url (nullable)
- is_active

partner_offers
- id
- partner_id (FK partners)
- title
- description
- target_audience: enum [vendor, client, all]

lead_form_submissions
- id
- partner_offer_id (FK partner_offers)
- vendor_id (FK vendors, nullable si soumis par un client)
- form_data (JSON)
- consent_given (boolean, obligatoire)
- status: enum [nouveau, transmis, converti, rejete]
- created_at
```

### 3.10 Publicité

```
ad_slots
- id
- vendor_id (FK vendors, l'annonceur)
- slot_type: enum [banniere_accueil, produit_sponsorise, boutique_en_vedette]
- start_date, end_date
- budget_amount
- status: enum [actif, termine, en_attente_paiement]
```

---

## 4. Flows critiques

### 4.1 Commande simple (boutique)
1. Client parcourt le catalogue ou clique un lien perso vendeur (`/boutique/{slug}`)
2. Ajoute au panier → paiement via mobile money
3. Statut `paiement_sequestre` (l'argent est retenu, pas versé au vendeur)
4. Vendeur notifié → prépare la commande
5. Dispatch livreur (voir 4.2)
6. Livraison confirmée par le client (bouton "j'ai reçu ma commande") ou auto-libération après délai (ex: 48h sans litige)
7. Statut `paiement_libere` → le montant (moins commission) part dans le solde à verser au vendeur

### 4.2 Dispatch livreur ("premier arrivé, premier servi")
1. Commande passe en statut `cherche_livreur`
2. Notification (push + fallback WhatsApp/SMS après 30-60s sans réponse) à tous les livreurs disponibles dans un rayon de X km autour du vendeur
3. **Verrou atomique obligatoire** : utiliser une transaction DB avec `SELECT ... FOR UPDATE` ou un lock Redis sur `order_id` pour garantir qu'un seul livreur peut accepter
4. Premier à accepter → `courier_id` assigné, notification "commande déjà prise" aux autres
5. Si aucune acceptation après 2 minutes → élargir automatiquement le rayon de recherche
6. Livreur récupère la commande → statut `recuperee` → `en_livraison` → `livree` (mise à jour manuelle par le livreur, pas de tracking GPS continu au démarrage)

### 4.3 Commande via TikTok Live
1. Vendeuse partage son lien perso pendant le live (`source=tiktok_live` capturé automatiquement via paramètre UTM-like)
2. Mode "commande rapide" : formulaire minimal (nom, téléphone, adresse, article numéro X) sans création de compte complète
3. Le reste suit le flow standard (paiement séquestre, dispatch, etc.)
4. Le vendeur peut voir dans son dashboard Filament la répartition des commandes par source (live vs organique)

### 4.4 Onboarding vendeur avec vérification progressive
1. Inscription : téléphone, nom commerce, type d'activité, adresse
2. Upload CNI obligatoire → statut `identite_confirmee` après vérification manuelle (appel/visite ou vérification photo)
3. Option : upload RCCM/DFE → statut `verifie` (badge fort sur le profil)
4. Si le vendeur n'a pas de RCCM : proposer le service d'accompagnement (`rccm_assist_status`), avec suivi d'étapes visible dans son dashboard, similaire à un tracking de colis

### 4.5 Litige ("je n'ai pas reçu ma commande")
1. Client ouvre un litige depuis la commande → paiement bloqué en `sequestre` (pas de libération automatique)
2. Notification à l'équipe modération (ou vendeur directement pour un premier niveau de résolution)
3. Résolution manuelle → remboursement client OU libération au vendeur

### 4.6 Leads partenaires
1. Vendeur consulte l'espace "Services" → sélectionne une offre (ex: micro-crédit)
2. Formulaire pré-rempli si possible avec les données d'activité du vendeur sur la plateforme (avec son consentement explicite — case à cocher obligatoire)
3. Soumission → `lead_form_submissions` créé → transmission automatique ou filtrée manuellement selon le partenaire
4. Dashboard Filament pour suivre le volume de leads par partenaire (base de négociation commerciale)

---

## 5. Endpoints API principaux (aperçu)

```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/otp/verify        # vérification par SMS/WhatsApp

GET    /api/vendors/{slug}          # page boutique publique (lien perso)
GET    /api/vendors/{id}/listings

POST   /api/orders                  # création de commande
GET    /api/orders/{id}
POST   /api/orders/{id}/confirm-receipt   # libère le paiement séquestre
POST   /api/orders/{id}/dispute

POST   /api/payments/initiate
POST   /api/payments/webhook        # callback de l'agrégateur mobile money

POST   /api/vendor/profile          # onboarding vendeur (business_name, type, adresse...)
POST   /api/courier/profile         # onboarding livreur (vehicle_type, documents)
POST   /api/courier/availability    # toggle disponibilité
GET    /api/courier/orders/available   # liste d'attente des commandes cherche_livreur
POST   /api/courier/orders/{id}/accept
POST   /api/courier/orders/{id}/status   # mise à jour statut livraison

GET    /api/partners/offers
POST   /api/partners/offers/{id}/leads

POST   /api/reviews
```

---

## 6. Roadmap par phases

### Phase 1 — MVP cœur (semaines 1-6)
- Auth (téléphone + OTP)
- Vendeurs : inscription, catalogue simple, upload photos
- Client : parcours catalogue, panier, commande
- Paiement mobile money (sans séquestre dans un premier temps, ou séquestre simplifié)
- Dispatch livreur basique (peut être manuel/semi-manuel au tout début : liste d'attente affichée aux livreurs plutôt que notification poussée automatique)
- Filament admin pour vous (gestion vendeurs, commandes)
- PWA déployée dès que l'API est stable (mise en ligne immédiate, sans délai de publication)
- Développement de l'app Flutter en parallèle sur la même API (comptes développeur Apple/Google déjà en place, donc pas de délai administratif à anticiper)
- Lancement pilote avec les boutiques déjà intéressées + quelques vendeuses de nourriture

### Phase 2 — Confiance renforcée (semaines 7-12)
- Paiement séquestre complet + système de litige
- Vérification vendeur à paliers (identité confirmée / vérifié) avec upload documents
- Accompagnement RCCM (tunnel de suivi)
- Dispatch livreur automatisé avec verrou atomique et élargissement de rayon
- Notifications WhatsApp Business API
- Avis/notation bidirectionnelle
- Lien perso vendeur + mode commande rapide pour TikTok Live
- Codes promo par vendeur

### Phase 3 — Publication app & scale (semaines 13-20)
- Soumission et publication de l'app Flutter sur l'App Store et le Play Store (développée depuis la Phase 1, comptes déjà en place donc soumission dès que l'app est prête)
- Codes produits numérotés pour les lives
- Cash-out livreur automatisé
- Bouton d'urgence livreur
- KYC livreur renforcé

### Phase 4 — Monétisation additionnelle (au-delà, une fois le volume établi)
- Espace publicitaire (bannières, produits sponsorisés)
- Module leads partenaires (banque, assurance, gestion de patrimoine)
- Score vendeur basé sur l'activité (pour appuyer les demandes de micro-crédit)

---

## 7. Points d'attention légaux (à valider avec un juriste local)

- Les partenariats banque/assurance/patrimoine doivent rester en mode **apport d'affaires** (formulaire de lead + redirection), jamais de conseil personnalisé ni de traitement de fonds par la plateforme elle-même — activités réglementées par la BCEAO (banque) et la CIMA (assurance) en zone UEMOA.
- Le consentement explicite du vendeur est requis avant toute transmission de ses données à un partenaire.
- Statut de la société DjidjiMarket elle-même à formaliser (RCCM + DFE), voir démarches CEPICI.
- Le séquestre de paiement (vous détenez temporairement l'argent du client) peut avoir des implications réglementaires selon le volume — à valider avec l'agrégateur de paiement (CinetPay/PayDunya) et un juriste, notamment si les montants agrégés deviennent significatifs.

---

## 8. Identité de marque

Charte graphique v1.0 (juillet 2026), fichiers sources dans `public/images/` (`DjidjiMarket-Charte-Graphique.pdf` + variantes du logo). Tagline : *« le vrai marché, en toute confiance »*.

**Couleurs :**

| Couleur | Hex | Usage |
|---|---|---|
| Vert Djidji | `#204E29` | Icône, mot "djidji", CTA principaux, confiance/vérification — doit dominer visuellement |
| Orange Djidji | `#D56E2B` | Sac du panier, mot "market", accents, badges promo — ponctuation uniquement, jamais à parts égales avec le vert |
| Texte principal | `#222222` | Corps de texte, titres secondaires |
| Fond neutre | `#F2F2F0` | Arrière-plans clairs, cartes, séparateurs |

**Typographie :** Poppins Bold (titres, CTA principaux), Poppins Medium/Regular (sous-titres, nav, boutons secondaires), Inter ou Nunito Sans Regular (corps de texte, formulaires).

**Logo :** espace de protection minimum = hauteur du "d" minuscule ; largeur minimale 30 mm print / 120 px écran ; versions fond clair et fond sombre disponibles (monochrome blanc/noir). Ne jamais étirer, recolorer, ombrer, ni placer sur un fond chargé.

**État d'application :** intégré au panel Filament admin (`AdminPanelProvider`) — couleur primaire, logo clair/sombre, favicon. Pas encore appliqué à un front public (PWA non démarrée, voir section 11).

---

## 9. Sécurité API

Décisions de sécurité prises lors de l'implémentation des endpoints de la section 5, à respecter pour toute extension future de l'API.

**Authentification :**
- Sanctum (tokens API), téléphone comme identifiant principal + vérification OTP.
- L'inscription publique (`POST /api/auth/register`) ne peut auto-attribuer que les rôles `client`, `vendor`, `courier` — `admin` et `partner_manager` restent réservés à une attribution interne (jamais via un endpoint public), pour empêcher toute élévation de privilège.
- Réinscrire un numéro déjà utilisé mais non-vérifié ne fait que renvoyer un nouvel OTP — ça n'écrase jamais le mot de passe/rôle existant. Sans cette règle, quelqu'un connaissant juste le numéro de téléphone d'un tiers pourrait détourner son inscription en cours avant qu'il ne la vérifie.
- Les requêtes API non authentifiées renvoient toujours un 401 JSON propre (pas de tentative de redirection vers une page de login web inexistante).

**OTP :**
- Codes à 6 chiffres, hashés en base (table `otp_codes`), expiration 10 minutes.
- Le code en clair n'est loggé (`Log::info`) qu'en environnement `local`/`testing`, jamais au-delà — pour ne pas fuiter d'OTP dans les logs une fois un vrai fournisseur SMS/WhatsApp branché en Phase 2.

**Rate limiting :**
- 60 req/min par utilisateur (ou IP si non authentifié) sur l'ensemble de l'API (`RateLimiter::for('api', ...)`).
- 5 req/min par IP sur les routes `/api/auth/*` pour limiter le brute-force et le spam d'OTP.

**Paiement :**
- `POST /api/payments/webhook` est un endpoint public (appelé par l'agrégateur) protégé par un secret partagé transmis en en-tête `X-Webhook-Secret`, comparé avec `hash_equals` (config `services.payment_aggregator.webhook_secret`, variable d'env `PAYMENT_WEBHOOK_SECRET`). À remplacer par le vrai schéma de signature CinetPay/PayDunya lors de l'intégration réelle.

**Exposition de données :**
- Le profil vendeur public (`GET /api/vendors/{slug}`) exclut délibérément les champs internes : numéros RCCM/DFE, URLs des documents de vérification, `commission_rate`.
- Le panel Filament admin est restreint aux utilisateurs `role=admin` via `User::canAccessPanel()`.

---

## 10. Stratégie de tests

- Tests Feature (`tests/Feature/Api/`) via le client de test HTTP de Laravel + `Sanctum::actingAs()` pour les requêtes authentifiées — pas de tests unitaires isolés sur la logique métier, elle est simple et directement vérifiée par les tests d'intégration.
- Suite de tests sur SQLite en mémoire (rapide, isolé), base de développement réelle sur PostgreSQL — les deux sont vérifiées à chaque changement de schéma pour repérer les écarts de comportement entre moteurs (ex : enums, valeurs par défaut).
- `RefreshDatabase` sur chaque classe de test.
- Couverture actuelle : flow auth complet (inscription/OTP/connexion + cas de sécurité comme la tentative de détournement par ré-inscription), onboarding vendeur + visibilité du catalogue public (actif/inactif, champs internes masqués), création de commande (calcul du total/commission, validation du stock, rejet d'articles d'un autre vendeur), flow de paiement (cash vs mobile money, webhook, libération d'escrow), acceptation atomique "premier arrivé" côté livreur + séquence de transition de statut. Pages Filament testées pour l'accès restreint aux admins et le rendu des pages index/create/edit.

---

## 11. État d'avancement — Phase 1

**Fait :** modèle de données, migrations, modèles Eloquent avec relations ; panel Filament (Vendors, Listings, Orders) ; endpoints API auth/OTP, onboarding vendeur et livreur, catalogue public, création de commande, paiement avec séquestre simplifié, liste d'attente + acceptation/statut livreur ; identité de marque appliquée au panel admin.

**Écarts connus vis-à-vis de la section 6 :**
- PWA non démarrée.
- App Flutter non démarrée.
- Lancement pilote (hors périmètre code).

---

## 12. Notes d'usage de ce document avec Claude Code

- Donner ce document en contexte à chaque nouvelle session Claude Code pour éviter les incohérences d'architecture d'une session à l'autre.
- Démarrer par les migrations Laravel correspondant à la section 3, puis les modèles Eloquent avec les relations, avant d'attaquer les endpoints de la section 5.
- Prioriser strictement selon les phases de la section 6 — ne pas implémenter la section 7/monétisation additionnelle avant d'avoir un MVP fonctionnel avec de vrais vendeurs actifs.
- Tenir la section 11 à jour à mesure que les écarts se comblent ou que de nouveaux apparaissent.
