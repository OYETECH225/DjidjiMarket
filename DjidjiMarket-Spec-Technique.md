# DjidjiMarket — Spécification technique

**Version 1.2 — Document de référence pour développement avec Claude Code**

*v1.1 : ajout des sections 8-11 (identité de marque, sécurité API, stratégie de tests, état d'avancement Phase 1) reflétant les décisions prises lors de l'implémentation. Sections 1-7 inchangées.*
*v1.2 : ajout de la section 8 (page d'accueil, référence validée), champs `sale_price`/`sale_ends_at` en 3.3, endpoints accueil en section 5. Sections 8-11 de la v1.1 renumérotées 9-12.*

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

**Système de design :** voir `DjidjiMarket-DESIGN-System.md` (fourni séparément) — tokens de couleurs Material 3 prêts à mapper directement sur `ColorScheme` en Flutter (primary/onPrimary/primaryContainer/secondary/onSecondary...), typographie (Plus Jakarta Sans, police officielle de la marque), rayons d'arrondi, espacements et règles de composants (boutons, cartes, badges vérifié). À donner à Claude Code en même temps que ce document lors de la mise en place du thème de l'app. *(Note : ce fichier n'existe pas encore dans le repo au moment de la fusion — à fournir.)*

| Composant | Choix | Justification |
|---|---|---|
| Backend / API | Laravel (PHP) | Écosystème mature, Sanctum pour l'auth API |
| Admin & back-office vendeur | Filament (sur Laravel) | Panneau CRUD généré rapidement, gagne un temps considérable |
| Base de données | PostgreSQL (choisi) | Types avancés utilisés dès Phase 1 (JSON pour `photo_urls`, decimal geo) |
| App mobile | Flutter, développée en parallèle du web dès la Phase 1 (le développement étant assuré par Claude Code, le temps de code n'est plus le facteur limitant — voir note en fin de tableau) | Un seul code pour iOS/Android, bonnes perfs sur téléphones d'entrée de gamme |
| Web | PWA en Blade + Livewire + Alpine.js (dans le même repo Laravel), déployée en premier car sans délai de publication | Pas de build JS séparé ni de second projet à maintenir ; réutilise directement les modèles Eloquent et les services métier (`OrderService`, `PaymentService`) partagés avec l'API ; Livewire déjà présent comme dépendance de Filament |
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
- sale_price (nullable — si renseigné avec sale_ends_at, indique une vente flash active)
- sale_ends_at (nullable timestamp — si renseigné avec sale_price, l'article apparaît dans "Vente flash" avec le prix barré/promo jusqu'à cette date)
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

### 3.11 Sessions live (TikTok)

```
live_sessions
- id
- vendor_id (FK vendors)
- platform: enum [tiktok]
- live_url (lien direct vers le live ou le profil du vendeur)
- is_active (boolean — activé par le vendeur quand il démarre son live)
- started_at
- ended_at (nullable)
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

### 4.7 Rejoindre un live TikTok depuis l'app
1. Un vendeur démarre son live TikTok et active son statut dans l'app (`live_sessions.is_active = true`, `live_url` renseigné)
2. La page d'accueil affiche automatiquement un bandeau "DIRECT LIVE" mettant en avant ce vendeur (un seul live à la fois en priorité, ou rotation si plusieurs vendeurs sont en direct simultanément)
3. Le client tape "Rejoindre" → ouverture du live TikTok (deep link vers l'app TikTok si installée, sinon navigateur)
4. Le vendeur continue de partager son lien perso (`/boutique/{slug}`) pendant le live pour les commandes — le bandeau in-app sert de rappel/découverte pour les clients qui n'étaient pas déjà en train de regarder
5. `is_active` repasse à `false` automatiquement après un délai sans mise à jour (ex: 3h) pour éviter d'afficher un live terminé

---

## 5. Endpoints API principaux (aperçu)

```
POST   /api/auth/register
POST   /api/auth/login
POST   /api/auth/otp/verify        # vérification par SMS/WhatsApp

GET    /api/vendors                 # liste des boutiques actives (découverte, nécessaire à l'app Flutter), filtrable par ?type=
GET    /api/vendors/{slug}          # page boutique publique (lien perso)
GET    /api/vendors/{id}/listings
GET    /api/dishes-of-the-day       # plats du jour actifs, tous vendeurs actifs confondus (accueil)
GET    /api/flash-sales             # ventes flash actives, tous vendeurs actifs confondus (accueil)

GET    /api/orders                  # commandes du client connecté
POST   /api/orders                  # création de commande
GET    /api/orders/{id}
POST   /api/orders/{id}/confirm-receipt   # libère le paiement séquestre
POST   /api/orders/{id}/dispute

POST   /api/payments/initiate
POST   /api/payments/webhook        # callback de l'agrégateur mobile money

POST   /api/vendor/profile          # onboarding vendeur (business_name, type, adresse...)
GET    /api/vendor/me               # profil vendeur (vue propriétaire, inclut commission_rate/is_active)
PATCH  /api/vendor/me               # ex: toggle is_active
GET    /api/vendor/orders           # commandes reçues par le vendeur connecté
GET    /api/vendor/listings         # catalogue du vendeur connecté (actifs + inactifs)
POST   /api/vendor/listings         # créer un article
PUT    /api/vendor/listings/{id}    # modifier un article (vérifie la propriété)
DELETE /api/vendor/listings/{id}    # supprimer un article (vérifie la propriété)
POST   /api/courier/profile         # onboarding livreur (vehicle_type, documents)
GET    /api/courier/me              # profil livreur (vue propriétaire)
GET    /api/courier/orders          # commandes assignées à ce livreur (en cours + historique)
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

## 8. Page d'accueil (client) — référence validée

Maquette validée (générée sur Stitch à partir d'un prompt basé sur cette spec), structure de haut en bas :

1. **Barre du haut** : logo DjidjiMarket (icône panier + wordmark — utiliser le fichier de marque officiel, pas une réinterprétation) + sélecteur de zone/quartier (ex: "Cocody") avec icône de localisation
2. **Barre de recherche** : placeholder "Rechercher une boutique, un plat..."
3. **Hero** : titre + sous-titre + 2 CTA ("Découvrir les boutiques" / "Comment ça marche")
4. **Bandeau de confiance** : 3 cartes (paiement protégé par escrow, vendeurs vérifiés, livraison rapide)
5. **Vente flash** : cartes produit avec prix barré, prix réduit en orange, date de fin — pilotée par `sale_price`/`sale_ends_at` sur `listings` (voir section 3.3), tous vendeurs actifs confondus
6. **Grille de catégories** : Boutiques / Nourriture / Restaurants (cartes cliquables, filtrent la liste de boutiques plus bas via `vendor_type`)
7. **Plats du jour** : nom du plat, sous-titre vendeur, prix en orange, bouton d'ajout rapide — `type = plat_du_jour` sur `listings`, tous vendeurs actifs confondus
8. **Liste des boutiques** : filtrable par catégorie, respecte le filtre choisi en 6
9. **Bandeau live TikTok** : n'apparaît que si un vendeur a un live actif (voir flow 4.7) — **Phase 2, volontairement absent de l'implémentation actuelle**
10. **Navigation basse** (mobile) : Accueil / Panier / Commandes / Profil

**Écarts assumés vis-à-vis de la maquette d'origine, à corriger si un vrai besoin apparaît :**
- Pas de barre de recherche ni de sélecteur de zone/quartier — aucune recherche texte ni géolocalisation implémentée à ce jour, pas de champ mort affiché en attendant.
- Pas de section "Vendeurs en vedette" avec notes/avis — aucun système d'avis n'existe en base ; en construire un (`reviews`, note moyenne par vendeur) est un préalable avant de rétablir cette section avec de vraies données.
- Vente flash et Plats du jour restent toujours visibles, pas conditionnés à l'onglet de catégorie actif — ce sont des sections cross-vendeurs, pas propres à une catégorie.

**Points de vigilance pour l'implémentation (déjà respectés) :**
- Couleurs de la charte (`#204E29` vert, `#D56E2B` orange) codées en dur dans le thème de l'app, pas approximées depuis une maquette.
- Le bandeau live TikTok reste dynamique le jour où il sera construit (alimenté par `live_sessions`), jamais un bloc statique.

---

## 9. Identité de marque

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

**État d'application :** intégré au panel Filament admin (`AdminPanelProvider`) — couleur primaire, logo clair/sombre, favicon — et repris au thème PWA (`resources/css/app.css`) et Flutter (`lib/theme/app_theme.dart`), avec Plus Jakarta Sans à la place de Poppins/Inter suite au design system fourni ensuite (voir section 12).

---

## 10. Sécurité API

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

## 11. Stratégie de tests

- Tests Feature (`tests/Feature/Api/`) via le client de test HTTP de Laravel + `Sanctum::actingAs()` pour les requêtes authentifiées — pas de tests unitaires isolés sur la logique métier, elle est simple et directement vérifiée par les tests d'intégration.
- Tests des composants Livewire de la PWA (`tests/Feature/Livewire/`) via `Livewire::test()` / `Livewire::actingAs()` — vérifie le flow auth session, l'ajout au panier, la création de commande au checkout, et les autorisations sur le suivi de commande.
- Vérification visuelle ponctuelle des pages PWA via Playwright (captures d'écran en local, pas dans la suite de tests automatisée) pour valider le rendu réel de la charte graphique.
- Suite de tests sur SQLite en mémoire (rapide, isolé), base de développement réelle sur PostgreSQL — les deux sont vérifiées à chaque changement de schéma pour repérer les écarts de comportement entre moteurs (ex : enums, valeurs par défaut).
- `RefreshDatabase` sur chaque classe de test.
- Couverture actuelle : flow auth complet (inscription/OTP/connexion + cas de sécurité comme la tentative de détournement par ré-inscription), onboarding vendeur + visibilité du catalogue public (actif/inactif, champs internes masqués), création de commande (calcul du total/commission, validation du stock, rejet d'articles d'un autre vendeur), flow de paiement (cash vs mobile money, webhook, libération d'escrow), acceptation atomique "premier arrivé" côté livreur + séquence de transition de statut. Pages Filament testées pour l'accès restreint aux admins et le rendu des pages index/create/edit.

---

## 12. État d'avancement — Phase 1

**Fait :** modèle de données, migrations, modèles Eloquent avec relations ; panel Filament (Vendors, Listings, Orders) ; endpoints API auth/OTP, onboarding vendeur et livreur, catalogue public, création de commande, paiement avec séquestre simplifié, liste d'attente + acceptation/statut livreur ; identité de marque appliquée au panel admin ; **PWA (parcours client)** — inscription/OTP/connexion en session web, découverte des boutiques, page boutique publique (`/boutique/{slug}`), panier (session), checkout avec choix du mode de paiement, suivi de commande avec confirmation de réception ; logique métier partagée entre API et PWA via `OrderService`/`PaymentService`/`AuthService`/`OtpService`/`CartService`.

Ajout à l'état "fait" : **app Flutter (`mobile/`)** — même parcours client que la PWA (auth OTP, découverte boutiques, storefront, panier, checkout, suivi de commande) consommant l'API REST via un `ApiClient` (package `http`) et `flutter_secure_storage` pour le token, state management avec `provider`. A nécessité l'ajout de `GET /api/vendors` (liste des boutiques actives), absent de la section 5 d'origine — la PWA n'en avait pas besoin car server-rendue avec accès direct à Eloquent, mais Flutter n'a que l'API.

Ajout à l'état "fait" : **PWA (parcours vendeur)** — onboarding (création de la boutique après inscription), tableau de bord (statut de vérification, compteurs, visibilité on/off), gestion du catalogue (créer/modifier/activer/désactiver/supprimer ses propres articles avec upload photo), liste de ses commandes (lecture seule). Accès restreint par le rôle (`role:vendor` middleware) et par propriété (un vendeur ne peut agir que sur ses propres listings/commandes — vérifié par test). `Vendor::VERIFICATION_LABELS`/`VENDOR_TYPE_LABELS` centralisés sur le modèle et réutilisés par Filament, même pattern que `Order::STATUS_LABELS`.

Ajout à l'état "fait" : **app Flutter (parcours vendeur)** — même périmètre que la PWA vendeur (onboarding, tableau de bord, catalogue CRUD, liste de commandes), consommant de nouveaux endpoints ajoutés pour l'occasion (`GET/PATCH /api/vendor/me`, `GET /api/vendor/orders`, `GET/POST/PUT/DELETE /api/vendor/listings`) puisque Flutter n'a que l'API contrairement à la PWA. `VendorPortalService` côté Flutter, `VendorProfileResource` côté API (vue propriétaire, expose `commission_rate`/`is_active` que l'API publique masque volontairement).

**Design system :** `DjidjiMarket-DESIGN-System.md` (mobile) et `DjidjiMarket-DESIGN-System-web.md` (web, ajoute grille desktop/nav horizontale/sidebar sticky — mêmes tokens couleur/police/rayons que le mobile) ont été appliqués à la PWA et au thème Flutter : Plus Jakarta Sans, CTA principaux en orange plein (vert = action secondaire), aucune ombre (bordures 1px `outline-variant` à la place), cartes/inputs en rayon 24px. Des maquettes HTML (accueil, boutique, panier, suivi, commande rapide) ont aussi été fournies avec une structure plus riche (nav basse 5 onglets, bannières hero, filtres catégorie) — pas encore intégrées, voir écarts ci-dessous.

Ajout à l'état "fait" : **PWA (parcours livreur)** — onboarding (véhicule), tableau de bord (statut de vérification, toggle disponibilité, compteur de livraisons en cours), liste des commandes en attente d'un livreur avec acceptation atomique "premier arrivé", mes livraisons avec progression du statut (`livreur_assigne` → `recuperee` → `en_livraison` → `livree`). Logique d'acceptation/transition extraite dans `CourierDispatchService`, partagée avec l'API (le contrôleur API a été refactoré pour l'utiliser aussi, au lieu de dupliquer la logique de verrou atomique).

Ajout à l'état "fait" : **app Flutter (parcours livreur)** — même périmètre que la PWA livreur (onboarding, tableau de bord, commandes disponibles + acceptation, mes livraisons + progression du statut). Les trois parcours (client, vendeur, livreur) sont maintenant construits sur les deux plateformes.

**Bug trouvé et corrigé pendant ce chantier :** `VendorPortalService` avait été codé et utilisé par tous les écrans vendeur Flutter mais jamais déclaré dans `MultiProvider` (`main.dart`) — chaque écran vendeur aurait planté à l'exécution (`ProviderNotFoundException`) malgré `flutter analyze`/`flutter test` au vert, car le seul test existant ne montait que `HomeScreen`. Corrigé (ainsi que l'ajout de `CourierPortalService`, jamais oublié cette fois), et un test de régression ajouté qui résout chaque service depuis l'arbre de widgets réel de l'app pour détecter ce genre d'oubli à l'avenir.

Ajout à l'état "fait" : **refonte de l'accueil (PWA puis Flutter)**, d'après la section 8 — nav basse mobile (Accueil/Panier/Commandes/Profil, masquée sur les pages transactionnelles), écrans "Mes commandes" et "Profil" côté client (n'existaient pas), filtre de catégorie réel (`?type=` sur `GET /api/vendors`), section "Plats du jour" et section "Vente flash" cross-vendeurs (nouveaux endpoints publics `GET /api/dishes-of-the-day` et `GET /api/flash-sales`, requêtes centralisées dans `Listing::activeDishesOfTheDay()`/`activeFlashSales()` pour que PWA et API restent identiques). Dimensions de la page d'accueil desktop alignées sur la maquette Stitch fournie (conteneur 1280px, espacements 16/24/64px).

**Mécanisme de vente flash :** `sale_price`/`sale_ends_at` sur `listings` (section 3.3). `Listing::effectivePrice()` est l'unique source de vérité du prix facturé — `OrderService` et `CartService` (PWA et Flutter) l'utilisent tous les deux, donc le prix est toujours recalculé et verrouillé côté serveur à la création de la commande, jamais fait confiance au client. Exposé côté vendeur (Filament + formulaire PWA) avec validation (prix promo < prix normal, date de fin dans le futur, les deux champs vont ensemble).

**Écarts assumés vis-à-vis de la section 8 :** voir la liste "Écarts assumés" dans cette même section — pas de recherche/géoloc, pas de "Vendeurs en vedette" avec vraies notes (système d'avis inexistant), bandeau TikTok Live explicitement Phase 2.

**Écarts connus vis-à-vis de la section 6 :**
- Système d'avis/notation (`reviews`) : n'existe pas encore en base, bloque toute réintroduction honnête d'une section "Vendeurs en vedette" avec de vraies notes.
- Lancement pilote (hors périmètre code).

**Note d'environnement :** le SDK Flutter n'était pas installé au démarrage de ce chantier ; installé via `brew install --cask flutter`. Aucun SDK Android ni CocoaPods (iOS) n'est configuré sur cette machine — seule la cible web (Chrome) est disponible pour un lancement local. Rendu vérifié manuellement dans un vrai navigateur Chrome (boutique et logo visibles) ; la vérification automatisée via Playwright/Chromium headless n'a pas fonctionné dans ce sandbox (rendu WebGL/CanvasKit qui reste bloqué), à noter comme limite d'outillage plutôt que de code si ça se reproduit.

**Note de test Flutter :** dans les tests widgets, `pumpWidget()`/`pumpAndSettle()` vident la file de microtâches plusieurs fois autour de la construction d'une frame (`AutomatedTestWidgetsFlutterBinding.pump`), et le faux client HTTP des tests répond en microtâche plutôt qu'avec un vrai délai — l'état transitoire ("en chargement") d'un `FutureBuilder` alimenté par un appel réseau n'est donc pas garanti observable, et devient une vraie course dès que l'écran fait plusieurs appels concurrents (vérifié empiriquement : deux exécutions strictement identiques du même test ont produit des arbres de widgets différents). Ne pas écrire de test qui dépend de cet état transitoire précis.

---

## 13. Notes d'usage de ce document avec Claude Code

- Donner ce document en contexte à chaque nouvelle session Claude Code pour éviter les incohérences d'architecture d'une session à l'autre.
- Démarrer par les migrations Laravel correspondant à la section 3, puis les modèles Eloquent avec les relations, avant d'attaquer les endpoints de la section 5.
- Prioriser strictement selon les phases de la section 6 — ne pas implémenter la section 7/monétisation additionnelle avant d'avoir un MVP fonctionnel avec de vrais vendeurs actifs.
- Tenir la section 12 à jour à mesure que les écarts se comblent ou que de nouveaux apparaissent.
