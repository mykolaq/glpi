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

use Glpi\Exception\Http\AccessDeniedHttpException;
use Glpi\Exception\Http\BadRequestHttpException;

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

global $CFG_GLPI;

if (!Session::haveRight('datacenter', UPDATE)) {
    throw new AccessDeniedHttpException();
}

$racks_id = (int) ($_POST['racks_id'] ?? 0);
$itemtype = (string) ($_POST['itemtype'] ?? '');
$items_id = (int) ($_POST['items_id'] ?? 0);
$value = (int) ($_POST['value'] ?? 0);
$rand = (int) ($_POST['rand'] ?? mt_rand());

if ($racks_id > 0) {
    $rack = new Rack();
    if (!$rack->can($racks_id, READ)) {
        throw new AccessDeniedHttpException();
    }
}

if ($itemtype !== '' && !in_array($itemtype, $CFG_GLPI['rackable_types'], true)) {
    throw new BadRequestHttpException();
}

Item_Rack::showPositionDropdown($racks_id, $itemtype, $items_id, $value, $rand);
