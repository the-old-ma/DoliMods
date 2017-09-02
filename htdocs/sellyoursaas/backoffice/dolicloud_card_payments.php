<?php
/* Copyright (C) 2004-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 *
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

/**
 *       \file       htdocs/sellyoursaas/backoffice/dolicloud_card.php
 *       \ingroup    societe
 *       \brief      Card of a contact
 */

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/main.inc.php");
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php");
// Try main.inc.php using relative path
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

require_once(DOL_DOCUMENT_ROOT."/comm/action/class/actioncomm.class.php");
require_once(DOL_DOCUMENT_ROOT."/contact/class/contact.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/company.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/date.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/html.formcompany.class.php");
dol_include_once("/sellyoursaas/core/lib/dolicloud.lib.php");
dol_include_once('/sellyoursaas/class/dolicloudcustomernew.class.php');
dol_include_once('/sellyoursaas/class/cdolicloudplans.class.php');

$langs->load("admin");
$langs->load("companies");
$langs->load("users");
$langs->load("other");
$langs->load("commercial");
$langs->load("sellyoursaas@sellyoursaas");

$action		= (GETPOST('action','alpha') ? GETPOST('action','alpha') : 'view');
$confirm	= GETPOST('confirm','alpha');
$backtopage = GETPOST('backtopage','alpha');
$id			= GETPOST('id','int');
$instanceold   = GETPOST('instanceold','alpha');
$instanceoldid = GETPOST('instanceoldid','int');

$error=0; $errors=array();


if (empty($instanceoldid) && $action != 'create')
{
	$object = new Contract($db);
}
else
{
	$db2=getDoliDBInstance('mysqli', $conf->global->DOLICLOUD_DATABASE_HOST, $conf->global->DOLICLOUD_DATABASE_USER, $conf->global->DOLICLOUD_DATABASE_PASS, $conf->global->DOLICLOUD_DATABASE_NAME, $conf->global->DOLICLOUD_DATABASE_PORT);
	if ($db2->error)
	{
		dol_print_error($db2,"host=".$conf->db->host.", port=".$conf->db->port.", user=".$conf->db->user.", databasename=".$conf->db->name.", ".$db2->error);
		exit;
	}

	$object = new DoliCloudCustomerNew($db,$db2);
}


// Security check
$result = restrictedArea($user, 'sellyoursaas', 0, '','sellyoursaas');

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array array
include_once(DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php');
$hookmanager=new HookManager($db);


if ($id > 0 || $instanceoldid > 0)
{
	$result=$object->fetch($id?$id:$instanceoldid);
	if ($result < 0) dol_print_error($db,$object->error);
}

$backupstring=$conf->global->DOLICLOUD_SCRIPTS_PATH.'/backup_instance.php '.$object->instance.' '.$conf->global->DOLICLOUD_INSTANCES_PATH;



/*
 *	Actions
*/

$parameters=array('id'=>$id, 'objcanvas'=>$objcanvas);
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks

if (empty($reshook))
{
	// Cancel
	if (GETPOST("cancel") && ! empty($backtopage))
	{
		header("Location: ".$backtopage);
		exit;
	}

	include 'refresh_action.inc.php';

	$action = 'view';
}


/*
 *	View
 */

$help_url='';
llxHeader('',$langs->trans("DoliCloudInstances"),$help_url);

$form = new Form($db);
$form2 = new Form($db2);
$formcompany = new FormCompany($db);

$countrynotdefined=$langs->trans("ErrorSetACountryFirst").' ('.$langs->trans("SeeAbove").')';
$arraystatus=Dolicloudcustomernew::$listOfStatus;

if ($id > 0 || $instanceoldid > 0)
{
	// Show tabs
	$head = dolicloud_prepare_head($object);

	$title = $langs->trans("DoliCloudInstances");
	dol_fiche_head($head, 'payments', $title, 0, 'contract');
}

if (($id > 0 || $instanceoldid > 0) && $action != 'edit' && $action != 'create')
{
	/*
	 * Fiche en mode visualisation
	*/
	$newdb=getDoliDBInstance($conf->db->type, $object->instance.'.on.dolicloud.com', $object->username_db, $object->password_db, $object->database_db, 3306);
	if (is_object($newdb))
	{
		// Get user/pass of last admin user
		$sql="SELECT login, pass FROM llx_user WHERE admin = 1 ORDER BY statut DESC, datelastlogin DESC LIMIT 1";
		$resql=$newdb->query($sql);
		$obj = $newdb->fetch_object($resql);
		$object->lastlogin_admin=$obj->login;
		$object->lastpass_admin=$obj->pass;
		$lastloginadmin=$object->lastlogin_admin;
		$lastpassadmin=$object->lastpass_admin;
	}
//	else print 'Error, failed to connect';

	dol_htmloutput_errors($error,$errors);

	$savdb=$object->db;
	$object->db=$object->db2;	// To have ->db to point to db2 for showrefnav function
	dol_banner_tab($object,'instance','',1,'name','instance','','',1);
	$object->db=$savdb;


	print '<div class="fichecenter">';
	print '</div>';

}

if ($id > 0 || $instanceoldid > 0)
{
	dol_fiche_end();
}


if (($id > 0 || $instanceoldid > 0) && $action != 'edit' && $action != 'create')
{

	/*
	// Last refresh
	print $langs->trans("DateLastCheck").': '.($object->date_lastcheck?dol_print_date($object->date_lastcheck,'dayhour','tzuser'):$langs->trans("Never"));

	if (! $object->user_id && $user->rights->sellyoursaas->sellyoursaas->write)
	{
		print ' <a href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=refresh">'.img_picto($langs->trans("Refresh"),'refresh').'</a>';
	}*/
	print '<br>';

	if (empty($instanceoldid))
	{
		// ----- Backup instance -----
		print '<strong>INSTANCE SERVEUR '.$conf->global->SELLYOURSAAS_NAME.'</strong><br>';

		print '<div class="underbanner clearboth"></div>';
		print '<table class="border" width="100%">';

		// Nb of users
		print '<tr class="liste_titre">';
		print '<td>'.$langs->trans("Date").'</td>';
		print '<td>'.$langs->trans("Amount").'</td>';
		print '<td>'.$langs->trans("Status").'</td>';
		print '</tr>';

		print '<tr><td class="opacitymedium">'.$langs->trans("SeeOldBackOffice").'</td></tr>';

		print "</table><br>";


		// Barre d'actions
		/*	if (! $user->societe_id)
		 {
		 print '<div class="tabsAction">';

		 if ($user->rights->sellyoursaas->sellyoursaas->write)
		 {
		 print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=upgrade">'.$langs->trans('Upgrade').'</a>';
		 }

		 print "</div><br>";
		 }
		 */

		/*	// MySQL
		 $mysqlconnectstring='mysql -A -u '.$object->username_db.' -p\''.$object->password_db.'\' -h '.$object->hostname_db.' -D '.$object->database_db;
		 print 'Mysql connect string<br>';
		 print '<input type="text" name="mysqlconnectstring" value="'.$mysqlconnectstring.'" size="120"><br>';
		 */
	}


	if (! empty($instanceoldid))
	{
		// ----- DoliCloud instance -----
		print '<strong>INSTANCE DOLICLOUD v1</strong><br>';

		print '<div class="underbanner clearboth"></div>';
		print '<table class="border" width="100%">';

		// Nb of users
		print '<tr class="liste_titre">';
		print '<td>'.$langs->trans("Date").'</td>';
		print '<td>'.$langs->trans("Amount").'</td>';
		print '<td>'.$langs->trans("Status").'</td>';
		print '</tr>';

		print "</table>";

		print '<br>';
		print '<br>';
	}
}


llxFooter();

$db->close();