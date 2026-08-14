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
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * ---------------------------------------------------------------------
 */

require_once(__DIR__ . '/_check_webserver_config.php');

use Glpi\Exception\Http\BadRequestHttpException;
use Glpi\Exception\Http\NotFoundHttpException;
use Glpi\Exception\Http\AccessDeniedHttpException;

Session::checkCentralAccess();

$ira = new Item_Rack();
$rack = new Rack();

$get_source_item_url = static function (array $input): ?string {
    global $CFG_GLPI;

    $itemtype = (string) ($input['_from_itemtype'] ?? '');
    $items_id = (int) ($input['_from_items_id'] ?? 0);
    if (!in_array($itemtype, $CFG_GLPI['rackable_types'], true) || $items_id <= 0) {
        return null;
    }

    $item = getItemForItemtype($itemtype);
    if ($item === false || !$item->can($items_id, READ)) {
        return null;
    }

    return $itemtype::getFormURLWithID($items_id);
};

if (isset($_POST['update'])) {
    $ira->check($_POST['id'], UPDATE);
    //update existing relation
    if ($ira->update($_POST)) {
        $url = $get_source_item_url($_POST) ?? $rack->getFormURLWithID($_POST['racks_id']);
    } else {
        $url = $ira->getFormURLWithID($_POST['id']);
    }
    Html::redirect($url);
} elseif (isset($_POST['add'])) {
    $ira->check(-1, CREATE, $_POST);
    $ira->add($_POST);
    $url = $get_source_item_url($_POST) ?? $rack->getFormURLWithID($_POST['racks_id']);
    Html::redirect($url);
} elseif (isset($_POST['purge'])) {
    $ira->check($_POST['id'], PURGE);
    $racks_id = (int) $ira->fields['racks_id'];
    $ira->delete($_POST, true);
    $url = $get_source_item_url($_POST) ?? $rack->getFormURLWithID($racks_id);
    Html::redirect($url);
}

if (
    !isset($_GET['unit'])
    && !isset($_GET['orientation'])
    && !isset($_GET['rack'])
    && !isset($_GET['id'])
    && !(isset($_GET['itemtype']) && isset($_GET['items_id']))
) {
    throw new BadRequestHttpException();
}

$params = [];
if (isset($_GET['id'])) {
    $params['id'] = $_GET['id'];
    if (isset($_GET['_fixed_item'])) {
        $params['_fixed_item'] = true;
    }
} else {
    if (isset($_GET['itemtype'], $_GET['items_id'])) {
        global $CFG_GLPI;

        $itemtype = (string) $_GET['itemtype'];
        $items_id = (int) $_GET['items_id'];
        $item = getItemForItemtype($itemtype);
        if (
            !in_array($itemtype, $CFG_GLPI['rackable_types'], true)
            || $item === false
            || !$item->can($items_id, UPDATE)
        ) {
            throw new BadRequestHttpException();
        }
        $params = [
            'itemtype'    => $itemtype,
            'items_id'    => $items_id,
            'is_reserved' => 0,
            '_fixed_item' => true,
        ];
    } else {
        $params = [
            'racks_id'     => $_GET['racks_id'],
            'orientation'  => $_GET['orientation'],
            'position'     => $_GET['position'],
        ];
    }
    if (isset($_GET['_onlypdu'])) {
        $params['_onlypdu'] = $_GET['_onlypdu'];
    }
}
$ajax = isset($_REQUEST['ajax']);

if ($ajax) {
    $item = new Item_Rack();
    $id = $params['id'] ?? 0;
    if ($id > 0 && !$item->getFromDB($params['id'])) {
        throw new NotFoundHttpException();
    }
    if (($params['_fixed_item'] ?? false) && $id > 0) {
        $source_item = getItemForItemtype($item->fields['itemtype']);
        if (
            $source_item === false
            || !$source_item->can((int) $item->fields['items_id'], UPDATE)
            || !Session::haveRight('datacenter', UPDATE)
        ) {
            throw new AccessDeniedHttpException();
        }
    }
    $item->showForm($id, $params + ['no_header' => true]);
} else {
    $menus = ["assets", "rack"];
    Item_Rack::displayFullPageForItem($params['id'] ?? 0, $menus, $params);
}
