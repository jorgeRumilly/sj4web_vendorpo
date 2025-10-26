# Changelog

Tous les changements notables de ce projet seront documentés dans ce fichier.

Le format est basé sur [Keep a Changelog](https://keepachangelog.com/fr/1.0.0/),
et ce projet adhère au [Semantic Versioning](https://semver.org/lang/fr/).

## [1.0.1] - 2025-10-02

### ✨ Ajouté
- Configuration module avec interface admin pour activer/désactiver l'affichage multi-transporteur
- Option de seuil minimum configurable pour l'affichage des informations d'expédition (défaut: 2)
- Toggle JavaScript repliable pour les détails d'expédition sur la page transporteur
- Bouton "Manage Supplier Settings" dans la configuration pour accès rapide au CRUD fournisseurs
- Champs `show_email_on_pdf` et `show_phone_on_pdf` dans la table supplier_settings
- Script de migration `upgrade-1.0.1.php` pour ajout des nouveaux champs
- Affichage conditionnel basé sur la configuration et le nombre de colis
- Composant réutilisable `packages_list.tpl` pour l'affichage des colis
- Fichier `CLAUDE.md` pour documentation destinée à Claude Code
- Fichier `README.md` avec documentation complète utilisateur
- Fichier `CHANGELOG.md` pour suivi des versions

### 🔧 Modifié
- **Architecture PDF** : Refactorisation complète selon pattern PrestaShop 8.1
  - Remplacement de `Sj4webPdfVendorPO` par `Sj4webHTMLTemplateVendorPO` (extends `HTMLTemplate`)
  - Remplacement de `Sj4webPdfReturnSlip` par `Sj4webHTMLTemplateReturnSlip` (extends `HTMLTemplate`)
  - Utilisation de la classe standard `PDF` avec PDFGenerator
- **Traductions** : Migration vers syntaxe PrestaShop 8.1 standard
  - Remplacement `mod='modulename'` par `d='Modules.Sj4webvendorpo.{Admin|Shop}'`
  - Élimination des textes hardcodés dans `Sj4webVendorPoService`
  - Ajout système de traduction dans le service avec injection du module
- **UX Front-Office** : Amélioration complète de l'affichage
  - Structure de données `buildSupplierSummary()` : passage de `suppliers` à `packages`
  - Template checkout avec 2 modes distincts : `summary` et `carrier`
  - Design moderne avec cartes, badges et icônes Material Design
  - Affichage détaillé par colis avec numéro, délai et liste d'articles
- **Controller** : Adaptation `AdminSj4webVendorPoController` au nouveau pattern PDF
- Templates PDF divisés en composants modulaires (summary, products, addresses, notes)
- CSS responsive avec variables personnalisées pour cohérence visuelle

### 🐛 Corrigé
- **Erreur table multistore** : Suppression de `Shop::addTableAssociation()` non nécessaire
- **Nom fournisseur vide** : Correction mapping `fields_list` dans AdminSj4webSupplierSettingsController
- **Bouton "Modifier" inactif** : Correction population `$this->_list` avec statut configuré
- **Sélection fournisseur** : Logique dropdown ADD/EDIT corrigée
  - Mode ADD : Dropdown avec fournisseurs non configurés uniquement
  - Mode EDIT : Nom en lecture seule + champ hidden
- **Doublons** : Ajout validation anti-doublon dans `processAdd()`
- **Erreurs SSL local** : Override `assignCustomHeaderData()` pour éviter erreurs `getimagesize()` en dev
- Message "No supplier" remplacé par "Fournisseur non spécifié" traduit
- Gestion des produits sans fournisseur (id_supplier = 0) comme colis distinct

### 🗑️ Supprimé
- Classes obsolètes : `Sj4webPdfVendorPO.php` et `Sj4webPdfReturnSlip.php`
- Dépendance à `Sj4webSupplierPresenter` dans le rendu de liste admin
- Syntaxe traduction legacy PrestaShop 1.6/1.7

### 🔒 Sécurité
- Maintien de la validation des tokens admin sur toutes les actions
- Échappement HTML systématique dans tous les templates
- Validation ObjectModel renforcée

## [1.0.0] - 2025-10-01

### ✨ Ajouté - Version initiale
- Module de base pour PrestaShop 8.1+
- **Gestion Fournisseurs**
  - Interface CRUD complète pour paramètres fournisseurs
  - Table `ps_sj4web_supplier_settings` avec champs :
    - Informations société (company_name, contact_name)
    - Adresse complète (address1, address2, postcode, city, phone)
    - Délai de livraison (lead_time)
  - Statut "Configuré" / "Non configuré" avec badges visuels
  - Scope multistore (id_shop)
- **Bons de Commande PDF**
  - Hook `displayAdminOrderMain` sur page commande admin
  - Groupement automatique des produits par `id_supplier`
  - Génération PDF avec : références, EAN13, MPN, désignations, quantités
  - Exclusion des prix pour confidentialité
  - Numérotation format `PO-{ORDER_REF}-{ID_SUPPLIER}`
- **Affichage Front-Office**
  - Hook `displayCheckoutSummaryTop` : Résumé simple du nombre d'expéditions
  - Hook `displayBeforeCarrier` : Détails des colis avant sélection transporteur
  - Hook `displayOrderConfirmation` : Informations complètes sur page confirmation
  - Hook `displayShoppingCartFooter` : Prévu (non implémenté dans v1.0.0)
  - Exclusion automatique des produits virtuels
- **Bordereaux de Retour**
  - Génération PDF par fournisseur
  - Validation obligatoire de l'adresse de retour complète
  - Message d'erreur si adresse incomplète
- **Services**
  - `Sj4webVendorPoService` : Groupement produits et construction données BC
  - `Sj4webReturnService` : Gestion retours avec validation adresses
  - `Sj4webSupplierSettings` : ObjectModel avec méthodes helper
  - `Sj4webSupplierPresenter` : Préparation données pour templates
- **Contrôleurs Admin**
  - `AdminSj4webSupplierSettingsController` : CRUD paramètres fournisseurs
  - `AdminSj4webVendorPoController` : Actions génération PDF (BC + retours)
- **Base de Données**
  - Script `sql/install.php` : Création table supplier_settings
  - Script `sql/uninstall.php` : Suppression table
  - Contrainte UNIQUE sur (id_shop, id_supplier)
- **Traductions**
  - Support multilingue avec fichiers XLF
  - Langues supportées : Français (fr-FR) et Anglais (en-US)
  - Domaines : `Modules.Sj4webvendorpo.Admin` et `Modules.Sj4webvendorpo.Shop`
- **Hooks PrestaShop**
  - `displayAdminOrderMain` - Bloc génération BC sur commande
  - `displayCheckoutSummaryTop` - Résumé checkout
  - `displayShoppingCartFooter` - Panier (prévu)
  - `displayBeforeCarrier` - Avant choix transporteur
  - `displayOrderConfirmation` - Page confirmation
  - `displayHeader` - Enregistrement assets CSS/JS
- **Templates Smarty**
  - Admin : `order_block.tpl`
  - Front : `checkout_supplier_summary.tpl`, `order_confirmation_supplier.tpl`
  - PDF : `vendor_po.tpl`, `return_slip.tpl`
- **Assets**
  - CSS : `supplier_summary.css` avec variables personnalisées
  - JS : `supplier_summary.js` (prêt pour toggle)
- **Sécurité**
  - Tokens admin sur toutes les actions
  - Validation PrestaShop sur ObjectModel
  - Fichiers `index.php` de protection dans tous les dossiers
  - Échappement HTML dans templates

### 📋 Spécifications Respectées
- Produits virtuels exclus automatiquement
- Produits sans fournisseur traités comme supplier distinct (id=0)
- Références combinaisons prioritaires sur références produit
- Validation adresse retour obligatoire pour bordereaux
- Gestion des exceptions avec messages utilisateur clairs

---

## Légende des Types de Changements

- `✨ Ajouté` : Nouvelles fonctionnalités
- `🔧 Modifié` : Changements dans les fonctionnalités existantes
- `🐛 Corrigé` : Corrections de bugs
- `🗑️ Supprimé` : Fonctionnalités supprimées
- `🔒 Sécurité` : Corrections de vulnérabilités
- `📝 Documentation` : Mises à jour de documentation
- `⚡ Performance` : Améliorations de performance

---

## Roadmap Potentielle (Non Planifiée)

### Idées pour versions futures
- Export des bons de commande en CSV/Excel
- Envoi automatique des BCs par email aux fournisseurs
- Historique des BCs générés
- Statistiques par fournisseur
- Support d'autres langues (ES, IT, DE)
- Personnalisation avancée des templates PDF
- API pour intégration avec ERP externes
- Notifications automatiques aux clients sur l'avancement des expéditions

---

**Note :** Ce changelog est maintenu manuellement. Chaque version doit être documentée avec ses changements avant release.

**Format des dates :** AAAA-MM-JJ (ISO 8601)

**Développé par SJ4WEB.FR** - Tous droits réservés.
