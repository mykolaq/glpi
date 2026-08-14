<?php

/**
 * ---------------------------------------------------------------------
 *
 * GLPI - Gestionnaire Libre de Parc Informatique
 *
 * http://glpi-project.org
 *
 * @copyright 2015-2026 Teclib' and contributors.
 * @copyright 2003-2014 by the INDEPNET Development Team.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * ---------------------------------------------------------------------
 */

use Glpi\Exception\Http\AccessDeniedHttpException;
use Glpi\Exception\Http\BadRequestHttpException;

header('Content-Type: text/html; charset=UTF-8');
Html::header_nocache();

global $CFG_GLPI;

if (!Session::haveRight('datacenter', UPDATE)) {
    throw new AccessDeniedHttpException();
}

$level = (string) ($_POST['level'] ?? '');
$parent_id = (int) ($_POST['parent_id'] ?? 0);
$itemtype = (string) ($_POST['itemtype'] ?? '');
$items_id = (int) ($_POST['items_id'] ?? 0);
$rand = (int) ($_POST['rand'] ?? mt_rand());

if (!in_array($itemtype, $CFG_GLPI['rackable_types'], true)) {
    throw new BadRequestHttpException();
}

if ($level === 'dcroom') {
    if ($parent_id > 0 && !(new Datacenter())->can($parent_id, READ)) {
        throw new AccessDeniedHttpException();
    }
    Item_Rack::showDCRoomDropdown($parent_id, 0, $rand, $itemtype, $items_id);
    echo Html::scriptBlock("$('#rack_select_$rand, #rack_position_$rand').empty();");
} elseif ($level === 'rack') {
    if ($parent_id > 0 && !(new DCRoom())->can($parent_id, READ)) {
        throw new AccessDeniedHttpException();
    }
    Item_Rack::showRackDropdown($parent_id, 0, $rand, $itemtype, $items_id);
    echo Html::scriptBlock("$('#rack_position_$rand').empty();");
} else {
    throw new BadRequestHttpException();
}
