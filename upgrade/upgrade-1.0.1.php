<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Sj4web_vendorpo $module
 *
 * @return bool
 */
function upgrade_module_1_0_1($module)
{
    $columns = [
        'show_email_on_pdf' => 'tinyint(1) UNSIGNED default 0 after `lead_time`',
        'show_phone_on_pdf' => 'tinyint(1) UNSIGNED default 0 after `show_email_on_pdf`',
    ];
    return add_columns('sj4web_supplier_settings', $columns);
}


/**
 * Ajoute des colonnes à une table et optionnellement à sa table de langues (_lang).
 *
 * @param string $table Nom de la table sans préfixe
 * @param array|false $columns_to_add Associatif [nom_colonne => définition_sql] pour la table principale
 * @param array|false $columns_lang_to_add Associatif [nom_colonne => définition_sql] pour la table *_lang
 * @return bool True si toutes les opérations ont réussi, false sinon
 */
function add_columns($table = '', $columns_to_add = false, $columns_lang_to_add = false): bool
{
    $res = true;

    if (is_array($columns_to_add) && count($columns_to_add)) {
        $column_formated = get_fields_from_table($table);
        foreach ($columns_to_add as $name => $details) {
            if (!in_array($name, $column_formated)) {
                $success = Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . pSQL($table) . '` ADD COLUMN `' . pSQL($name) . '` ' . $details);
                $res = $res && $success;
            }
        }
    }

    if (is_array($columns_lang_to_add) && count($columns_lang_to_add)) {
        $column_formated = get_fields_from_table($table . '_lang');
        foreach ($columns_lang_to_add as $name => $details) {
            if (!in_array($name, $column_formated)) {
                $success = Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . pSQL($table) . '_lang` ADD COLUMN `' . pSQL($name) . '` ' . $details);
                $res = $res && $success;
            }
        }
    }

    return (bool) $res;
}

/**
 * Récupère la liste des champs d'une table (sans préfixe de la base).
 *
 * @param string $table Nom de la table sans préfixe.
 * @return array Tableau des noms de colonnes. Retourne un tableau vide en cas d'erreur ou si la table n'a pas de colonnes.
 *
 * Analyse :
 * - Utilise `SHOW FIELDS` pour obtenir la structure de la table.
 * - Si la requête échoue, `executeS` peut retourner false ; la fonction normalise alors en renvoyant un tableau vide.
 * - Améliorations possibles : journaliser les erreurs SQL, lever des exceptions ou utiliser un cache si la fonction est appelée fréquemment.
 * - Sécurité : on applique `pSQL` sur le nom de table pour réduire le risque d'injection dans l'identifiant, même si l'idéal serait de valider le nom contre une liste blanche.
 */
function get_fields_from_table($table)
{
    // Nettoyage basique du nom de table
    $table_name = pSQL($table);

    $sql = 'SHOW FIELDS FROM `' . _DB_PREFIX_ . $table_name . '`';
    $columns = Db::getInstance()->executeS($sql);

    $fields = [];

    // Vérifier que la réponse est bien un tableau avant d'itérer
    if ($columns && is_array($columns)) {
        foreach ($columns as $column) {
            if (isset($column['Field'])) {
                $fields[] = $column['Field'];
            }
        }
    }

    return $fields;
}