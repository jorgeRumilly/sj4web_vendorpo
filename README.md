# SJ4WEB - Vendor POs & Supplier Settings

Module PrestaShop 8.1+ pour la génération de bons de commande fournisseurs (Purchase Orders) et la gestion des paramètres fournisseurs avec adresses de retour.

![Version](https://img.shields.io/badge/version-1.0.1-blue.svg)
![PrestaShop](https://img.shields.io/badge/PrestaShop-8.1+-green.svg)
![License](https://img.shields.io/badge/license-Proprietary-red.svg)

## 📋 Description

Ce module permet de :
- **Générer des bons de commande fournisseurs** au format PDF depuis la page commande admin
- **Configurer les paramètres fournisseurs** (adresse de retour, coordonnées, délais de livraison)
- **Afficher les informations d'expédition multi-fournisseurs** sur le front-office (checkout et confirmation)
- **Générer des bordereaux de retour** avec validation des adresses configurées

### Fonctionnalités principales

✅ **Gestion Fournisseurs**
- Interface CRUD complète pour configurer chaque fournisseur
- Adresses de retour personnalisées par fournisseur
- Délais de livraison configurables
- Statut "Configuré" / "Non configuré" avec badges visuels

✅ **Bons de Commande PDF**
- Génération automatique depuis la page commande admin
- Groupement des produits par fournisseur (id_supplier)
- Contenu : références, EAN13, MPN, désignations, quantités
- **Sans prix** (pour confidentialité)
- Numérotation : `PO-{REFERENCE_COMMANDE}-{ID_FOURNISSEUR}`

✅ **Affichage Front-Office**
- Informations d'expédition sur la page checkout
- Détail des colis par fournisseur avec toggle repliable
- Affichage complet sur la page de confirmation
- Design moderne avec Material Icons
- Responsive mobile

✅ **Bordereaux de Retour**
- Génération par fournisseur avec validation obligatoire de l'adresse
- Contenu : articles retournés + adresse de retour configurée

## 🚀 Installation

### Méthode 1 : Via le Back-Office PrestaShop

1. Téléchargez le module
2. Dans le BO PrestaShop : **Modules** → **Module Manager** → **Uploader un module**
3. Sélectionnez l'archive ZIP du module
4. Cliquez sur **Installer**

### Méthode 2 : Installation manuelle

1. Décompressez l'archive dans le dossier `/modules/` de votre PrestaShop
2. Assurez-vous que le dossier s'appelle `sj4web_vendorpo`
3. Dans le BO PrestaShop : **Modules** → **Module Manager**
4. Recherchez "SJ4WEB - Vendor POs"
5. Cliquez sur **Installer**

### Post-installation

Lors de l'installation, le module :
- ✅ Crée la table `ps_sj4web_supplier_settings` en base de données
- ✅ Enregistre les hooks nécessaires
- ✅ Crée l'onglet admin "Vendor POs & Supplier Settings" dans le menu Catalogue
- ✅ Configure les options par défaut (multi-carrier activé, seuil minimum : 2 envois)

## ⚙️ Configuration

### 1. Configuration Générale

**Accès :** Modules → Module Manager → SJ4WEB - Vendor POs → **Configurer**

Options disponibles :
- **Activer l'affichage multi-transporteur** : Active/désactive l'affichage des informations d'expédition sur le front-office
- **Nombre minimum d'envois** : Seuil à partir duquel afficher les informations (par défaut : 2)

### 2. Configuration des Fournisseurs

**Accès :** Catalogue → **Vendor POs & Supplier Settings**

Pour chaque fournisseur, vous pouvez configurer :
- Nom de la société
- Nom du contact
- Adresse complète (ligne 1, ligne 2, code postal, ville)
- Téléphone
- Délai de livraison (ex: "48 à 72h", "3 à 5 jours ouvrés")
- Affichage email/téléphone sur les PDFs

**Note :** Un fournisseur doit avoir une adresse complète pour pouvoir générer des bordereaux de retour.

## 📖 Utilisation

### Générer un Bon de Commande

1. Rendez-vous sur une page de commande dans le back-office
2. Descendez jusqu'à la section **"Vendor Purchase Orders"**
3. Vous verrez la liste des fournisseurs présents dans la commande
4. Cliquez sur **"Générer BC PDF"** pour le fournisseur souhaité
5. Le PDF se télécharge automatiquement

**Format du PDF :**
- En-tête avec logo et informations boutique
- Résumé de la commande et du bon de commande
- Adresses (boutique et retour fournisseur)
- Tableau des produits (référence, EAN13, MPN, désignation, quantité)
- Notes (optionnel)

### Affichage Front-Office

#### Page Checkout

**Étape 1 - Résumé :**
- Message simple indiquant le nombre d'expéditions séparées
- Note renvoyant vers l'étape transporteur pour plus de détails

**Étape 2 - Avant sélection transporteur :**
- Bouton toggle "Voir le détail" / "Masquer le détail"
- Liste détaillée des colis par fournisseur :
  - Numéro du colis
  - Délai de livraison
  - Liste des articles avec quantités et références

#### Page Confirmation

Affichage complet et non repliable des informations d'expédition :
- Nombre total d'envois avec badge visuel
- Détail de chaque colis avec tous les articles

**Conditions d'affichage :**
- Option "multi-carrier" activée dans la configuration
- Nombre de colis ≥ seuil minimum configuré

### Générer un Bordereau de Retour

1. Depuis la page de retour commande dans le back-office
2. Section **"Vendor Returns"**
3. Cliquez sur **"Générer Bordereau Retour"** pour le fournisseur
4. Le PDF se génère avec l'adresse de retour configurée

**⚠️ Attention :** Si l'adresse de retour du fournisseur n'est pas complète, une erreur s'affichera vous demandant de la configurer d'abord.

## 🗂️ Structure du Module

```
sj4web_vendorpo/
├── sj4web_vendorpo.php              # Classe principale du module
├── config.xml                        # Métadonnées
├── config_fr.xml                     # Métadonnées FR
├── logo.png                          # Logo du module
├── README.md                         # Ce fichier
├── CHANGELOG.md                      # Historique des versions
│
├── /classes/                         # Classes métier
│   ├── Sj4webSupplierSettings.php           # ObjectModel - Paramètres fournisseurs
│   ├── Sj4webVendorPoService.php            # Service - Agrégation données BC
│   ├── Sj4webReturnService.php              # Service - Bordereaux retour
│   ├── Sj4webSupplierPresenter.php          # Formattage données templates
│   └── /Pdf/
│       ├── Sj4webHTMLTemplateVendorPO.php   # Template HTML - Bon de commande
│       └── Sj4webHTMLTemplateReturnSlip.php # Template HTML - Bordereau retour
│
├── /controllers/admin/               # Contrôleurs admin
│   ├── AdminSj4webSupplierSettingsController.php  # CRUD fournisseurs
│   └── AdminSj4webVendorPoController.php           # Actions PDF
│
├── /sql/                             # Scripts base de données
│   ├── install.php                   # Création table
│   └── uninstall.php                 # Suppression table
│
├── /upgrade/                         # Scripts de migration
│   └── upgrade-1.0.1.php             # Migration v1.0.0 → v1.0.1
│
├── /views/
│   ├── /css/
│   │   └── supplier_summary.css      # Styles front-office
│   ├── /js/
│   │   └── supplier_summary.js       # Toggle JavaScript
│   └── /templates/
│       ├── /admin/
│       │   └── order_block.tpl       # Bloc page commande admin
│       ├── /front/
│       │   ├── checkout_supplier_summary.tpl        # Résumé checkout
│       │   ├── order_confirmation_supplier.tpl      # Confirmation
│       │   └── /_partials/
│       │       └── packages_list.tpl                # Composant liste colis
│       └── /pdf/
│           ├── supplier-vendorpo.tpl                # Layout BC
│           ├── supplier-vendorpo.summary-tab.tpl    # Onglet résumé BC
│           ├── supplier-vendorpo.products-tab.tpl   # Onglet produits BC
│           ├── supplier-vendorpo.addresses-tab.tpl  # Onglet adresses BC
│           ├── supplier-vendorpo.note-tab.tpl       # Onglet notes BC
│           └── return_slip.tpl                      # Bordereau retour
│
└── /translations/                    # Fichiers de traduction
    ├── fr.php                        # Legacy français
    ├── /fr-FR/
    │   ├── ModulesSj4webvendorpoAdmin.fr-FR.xlf
    │   └── ModulesSj4webvendorpoShop.fr-FR.xlf
    └── /en-US/
        ├── ModulesSj4webvendorpoAdmin.en-US.xlf
        └── ModulesSj4webvendorpoShop.en-US.xlf
```

## 🔧 Prérequis Techniques

- **PrestaShop :** 8.1.0 ou supérieur
- **PHP :** 7.2+ (recommandé 8.1+)
- **MySQL :** 5.6+ ou MariaDB 10.0+
- **Extensions PHP requises :**
  - PDO
  - GD (pour génération PDF avec logos)
  - mbstring

## 🗄️ Base de Données

### Table `ps_sj4web_supplier_settings`

| Colonne | Type | Description |
|---------|------|-------------|
| `id_sj4web_supplier_settings` | INT(11) | Clé primaire |
| `id_shop` | INT(11) | ID boutique (multistore) |
| `id_supplier` | INT(11) | ID fournisseur PrestaShop |
| `company_name` | VARCHAR(255) | Nom société fournisseur |
| `contact_name` | VARCHAR(255) | Nom du contact |
| `address1` | VARCHAR(255) | Adresse ligne 1 |
| `address2` | VARCHAR(255) | Adresse ligne 2 |
| `postcode` | VARCHAR(32) | Code postal |
| `city` | VARCHAR(128) | Ville |
| `phone` | VARCHAR(64) | Téléphone |
| `lead_time` | VARCHAR(255) | Délai de livraison |
| `show_email_on_pdf` | TINYINT(1) | Afficher email sur PDF |
| `show_phone_on_pdf` | TINYINT(1) | Afficher téléphone sur PDF |
| `date_add` | DATETIME | Date création |
| `date_upd` | DATETIME | Date modification |

**Contraintes :**
- UNIQUE KEY sur (`id_shop`, `id_supplier`)
- INDEX sur `id_shop`
- INDEX sur `id_supplier`

## 🌍 Multilingue

Le module supporte actuellement :
- 🇫🇷 Français (fr-FR)
- 🇬🇧 Anglais (en-US)

Les traductions utilisent le format XLF (XLIFF 1.2) standard de PrestaShop 8.1+.

## 🔒 Sécurité

- ✅ Validation tokens admin sur toutes les actions
- ✅ Échappement HTML sur toutes les sorties templates
- ✅ Validation des données via ObjectModel PrestaShop
- ✅ Protection contre injection SQL (requêtes préparées)
- ✅ Fichiers `index.php` de sécurité dans tous les dossiers
- ✅ Vérification des permissions PrestaShop

## ❓ FAQ

### Les produits virtuels sont-ils inclus dans les bons de commande ?

Non, les produits virtuels sont automatiquement exclus car ils ne nécessitent pas d'expédition physique.

### Que se passe-t-il si un produit n'a pas de fournisseur assigné (id_supplier = 0) ?

Il sera traité comme un colis séparé avec le libellé "Fournisseur non spécifié" ou équivalent selon la langue.

### Puis-je désactiver l'affichage des informations d'expédition sur le front-office ?

Oui, dans la configuration du module, désactivez l'option "Activer l'affichage multi-transporteur".

### Comment modifier le seuil d'affichage des informations d'expédition ?

Dans la configuration du module, modifiez le champ "Nombre minimum d'envois". Par défaut, les informations s'affichent à partir de 2 colis.

### Les prix apparaissent-ils sur les bons de commande PDF ?

Non, par design les PDFs ne contiennent **aucun prix** pour des raisons de confidentialité. Seules les références, quantités et informations produits sont incluses.

### Puis-je personnaliser les templates PDF ?

Oui, les templates sont dans `views/templates/pdf/`. Vous pouvez les modifier selon vos besoins tout en respectant la structure Smarty de PrestaShop.

## 🐛 Dépannage

### Le PDF ne se génère pas

- Vérifiez que le fournisseur est bien configuré dans "Vendor POs & Supplier Settings"
- Vérifiez que les produits ont un `id_supplier` assigné
- Consultez les logs PrestaShop dans `var/logs/`

### L'affichage front-office ne s'affiche pas

- Vérifiez que l'option "multi-carrier" est activée dans la configuration
- Vérifiez que le nombre de colis est supérieur ou égal au seuil configuré
- Videz le cache PrestaShop

### Erreur "Adresse de retour incomplète"

Complétez tous les champs obligatoires de l'adresse du fournisseur :
- company_name
- address1
- postcode
- city

## 📝 Support

Pour toute question ou assistance :
- **Email :** support@sj4web.fr
- **Site :** https://www.sj4web.fr

## 👨‍💻 Auteur

**SJ4WEB.FR**
- Développement de modules PrestaShop
- Solutions e-commerce sur mesure

---

## 📄 Licence

Ce module est un logiciel propriétaire développé par SJ4WEB.FR. Tous droits réservés.

L'utilisation de ce module est soumise aux conditions de la licence d'utilisation fournie avec le produit.

---

**Développé avec ❤️ pour PrestaShop 8.1+**
