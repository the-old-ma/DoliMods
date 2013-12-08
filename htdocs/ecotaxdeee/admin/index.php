<?php
/* Copyright (C) 2013 Laurent Destailleur  <eldy@users.sourceforge.net>
 */

/**
 *	    \file       htdocs/ecotaxdeee/admin/index.php
 *      \ingroup    ecotaxdee
 *      \brief      Setup page for ecotaxdeee module
 */

define('NOCSRFCHECK',1);

$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res && file_exists("../../../../main.inc.php")) $res=@include("../../../../main.inc.php");
if (! $res && file_exists("../../../../../main.inc.php")) $res=@include("../../../../../main.inc.php");
if (! $res && preg_match('/\/nltechno([^\/]*)\//',$_SERVER["PHP_SELF"],$reg)) $res=@include("../../../../dolibarr".$reg[1]."/htdocs/main.inc.php"); // Used on dev env only
if (! $res) die("Include of main fails");
require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/date.lib.php");
require_once(DOL_DOCUMENT_ROOT.'/core/class/html.formadmin.class.php');
require_once(DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php');
dol_include_once("/ecotaxdeee/lib/ecotaxdeee.lib.php");

if (!$user->admin) accessforbidden();

$langs->load("ecotaxdeee@ecotaxdeee");
$langs->load("admin");
$langs->load("other");

$def = array();
$action=GETPOST("action");


/*
 * Actions
 */
if ($action == 'save')
{
    $db->begin();

    $res=dolibarr_set_const($db,'ECOTAXDEE_USE_ON_CUSTOMER_ORDER',trim($_POST["ECOTAXDEE_USE_ON_CUSTOMER_ORDER"]),'chaine',0,'',$conf->entity);
    $res=dolibarr_set_const($db,'ECOTAXDEE_USE_ON_PROPOSAL',trim($_POST["ECOTAXDEE_USE_ON_PROPOSAL"]),'chaine',0,'',$conf->entity);
    $res=dolibarr_set_const($db,'ECOTAXDEE_USE_ON_CUSTOMER_INVOICE',trim($_POST["ECOTAXDEE_USE_ON_CUSTOMER_INVOICE"]),'chaine',0,'',$conf->entity);

    if (! $error)
    {
        $db->commit();
        $mesg = "<font class=\"ok\">".$langs->trans("SetupSaved")."</font>";
    }
    else
    {
        $db->rollback();
        $mesg = "<font class=\"error\">".$langs->trans("Error")."</font>";
    }
}



/*
 * View
 */


$form=new Form($db);
$formadmin=new FormAdmin($db);
$formother=new FormOther($db);

$help_url='EN:Module_EcoTaxDee_EN|FR:Module_EcoTaxDee|ES:Modulo_EcoTaxDee';
//$arrayofjs=array('/includes/jquery/plugins/colorpicker/jquery.colorpicker.js');
//$arrayofcss=array('/includes/jquery/plugins/colorpicker/jquery.colorpicker.css');
$arrayofjs=array();
$arrayofcss=array();
llxHeader('',$langs->trans("Setup"),$help_url,'',0,0,$arrayofjs,$arrayofcss);

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("EcoTaxDeeSetup"),$linkback,'setup');
print '<br>';


$head=ecotaxdeee_prepare_head();

dol_fiche_head($head, 'tabsetup', $langs->trans("EcoTaxDeee"));


print '<form name="ecotaxdeeeconfig" action="'.$_SERVER["PHP_SELF"].'" method="post">';
print '<input type="hidden" name="action" value="save">';

$var=false;
print "<table class=\"noborder\" width=\"100%\">";

print "<tr class=\"liste_titre\">";
print '<td>'.$langs->trans("Parameter")."</td>";
print "<td>".$langs->trans("Value")."</td>";
print "</tr>";
// GETPOST("ECOTAXDEE_USE_ON_CUSTOMER_ORDER")
print "<tr ".$bc[$var].">";
print "<td>".$langs->trans("ECOTAXDEE_USE_ON_CUSTOMER_ORDER")."</td>";
print "<td>";
$selectedvalue=$conf->global->GETPOST("ECOTAXDEE_USE_ON_CUSTOMER_ORDER");
print $form->selectyesno("ECOTAXDEE_USE_ON_CUSTOMER_ORDER",GETPOST("ECOTAXDEE_USE_ON_CUSTOMER_ORDER"));
print "</td>";
print "</tr>";
// GETPOST("ECOTAXDEE_USE_ON_PROPOSAL")
print "<tr ".$bc[$var].">";
print "<td>".$langs->trans("ECOTAXDEE_USE_ON_PROPOSAL")."</td>";
print "<td>";
$selectedvalue=$conf->global->GETPOST("ECOTAXDEE_USE_ON_PROPOSAL");
print $form->selectyesno("ECOTAXDEE_USE_ON_PROPOSAL",GETPOST("ECOTAXDEE_USE_ON_PROPOSAL"));
print "</td>";
print "</tr>";
// GETPOST("ECOTAXDEE_USE_ON_CUSTOMER_INVOICE")
print "<tr ".$bc[$var].">";
print "<td>".$langs->trans("ECOTAXDEE_USE_ON_CUSTOMER_INVOICE")."</td>";
print "<td>";
$selectedvalue=$conf->global->GETPOST("ECOTAXDEE_USE_ON_CUSTOMER_INVOICE");
print $form->selectyesno("ECOTAXDEE_USE_ON_CUSTOMER_INVOICE",GETPOST("ECOTAXDEE_USE_ON_CUSTOMER_INVOICE"));
print "</td>";
print "</tr>";

print '</table>';
print '<br>';


print '<center>';
//print "<input type=\"submit\" name=\"test\" class=\"button\" value=\"".$langs->trans("TestConnection")."\">";
//print "&nbsp; &nbsp;";
print "<input type=\"submit\" name=\"save\" class=\"button\" value=\"".$langs->trans("Save")."\">";
print "</center>";

print "</form>\n";

dol_fiche_end();


dol_htmloutput_mesg($mesg);


// Show message
/*$message='';
$urlgooglehelp='<a href="http://www.google.com/calendar/embed/EmbedHelper_en.html" target="_blank">http://www.google.com/calendar/embed/EmbedHelper_en.html</a>';
$message.=$langs->trans("GoogleSetupHelp",$urlgooglehelp);
print info_admin($message);
*/

llxFooter();

$db->close();
?>