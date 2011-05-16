<?php
/* Copyright (C) 2008 Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * Licensed under the GNU GPL v3 or higher (See file gpl-3.0.html)
 */

/**
 *    	\file       htdocs/google/index.php
 *		\ingroup    google
 *		\brief      Main google area page
 *		\version    $Id: index.php,v 1.6 2011/05/16 17:25:56 eldy Exp $
 *		\author		Laurent Destailleur
 */

include("./pre.inc.php");
/*$res=0;
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../dolibarr/htdocs/main.inc.php")) $res=@include("../../../dolibarr/htdocs/main.inc.php");     // Used on dev env only
if (! $res && file_exists("../../../../dolibarr/htdocs/main.inc.php")) $res=@include("../../../../dolibarr/htdocs/main.inc.php");   // Used on dev env only
if (! $res && file_exists("../../../../../dolibarr/htdocs/main.inc.php")) $res=@include("../../../../../dolibarr/htdocs/main.inc.php");   // Used on dev env only
if (! $res) die("Include of main fails");
*/
require_once(DOL_DOCUMENT_ROOT."/lib/agenda.lib.php");

// Load traductions files
$langs->load("google");
$langs->load("companies");
$langs->load("other");

// Load permissions
$user->getrights('google');

// Get parameters
$socid = isset($_GET["socid"])?$_GET["socid"]:'';

// Protection quand utilisateur externe
if ($user->societe_id > 0)
{
    $action = '';
    $socid = $user->societe_id;
}

$MAXAGENDA=empty($conf->global->GOOGLE_AGENDA_NB)?5:$conf->global->GOOGLE_AGENDA_NB;



/*******************************************************************
* ACTIONS
*
* Put here all code to do according to value of "action" parameter
********************************************************************/

if ($_REQUEST["action"] == 'add')
{
	$myobject=new Skeleton_class($db);
	$myobject->prop1=$_POST["field1"];
	$myobject->prop2=$_POST["field2"];
	$result=$myobject->create($user);
	if ($result > 0)
	{
		// Creation OK
	}
	{
		// Creation KO
		$mesg=$myobject->error;
	}
}





/***************************************************
* PAGE
*
* Put here all code to build page
****************************************************/

llxHeader('','Google',"EN:Module_GoogleEn|FR:Module_Google|ES:Modulo_Google");

$form=new Form($db);



$head = calendars_prepare_head('');

dol_fiche_head($head, 'gcal', $langs->trans('Events'), 0, 'action');

$finaltext='';

$MAXAGENDA=empty($conf->global->GOOGLE_AGENDA_NB)?5:$conf->global->GOOGLE_AGENDA_NB;
$i=1;$found=0;
while ($i <= $MAXAGENDA)
{
    $paramkey='GOOGLE_AGENDA_NAME'.$i;
    $paramcolor='GOOGLE_AGENDA_COLOR'.$i;
    //print $paramkey;
    if (! empty($conf->global->$paramkey))
    {
        $found++;
        $addcolor=false;
        if (isset($_GET["nocal"]))
        {
            if ($_GET["nocal"] == $i) $addcolor=true;
        }
        else $addcolor=true;

        $link=dol_buildpath("/google/index.php",1)."?mainmenu=agenda&idmenu=".$_SESSION["idmenu"]."&nocal=".$i;

        $text='';
        $text.='<table class="nobordernopadding">';
        $text.='<tr valign="middle" class="nobordernopadding">';

        // Color of agenda
        $text.='<td style="padding-left: 4px; padding-right: 4px" nowrap="nowrap">';
        $box ='<!-- Box color '.$selected.' -->';
        $box.='<table style="border-collapse: collapse; margin:0px; padding: 0px; border: 1px solid #888888;';
        if ($addcolor) $box.=' background: #'.(preg_replace('/#/','',$conf->global->$paramcolor)).';';
        $box.='" width="12" height="10">';
        $box.='<tr class="nocellnopadd"><td></td></tr>';    // To show box
        $box.='</table>';
        $text.=$box;
        $text.='</td>';

        // Name of agenda
        $text.='<td>';
        $text.='<a class="vsmenu" href="'.$link.'">'.$conf->global->$paramkey.'</a>';
        $text.='</td></tr>';

        $text.='</table>';

        $finaltext.=$text;
    }
    $i++;
}
if ($found > 1)
{
    $link=dol_buildpath("/google/index.php",1)."?mainmenu=agenda&idmenu=".$_SESSION["idmenu"];

    $text='';
    $text.='<table class="nobordernopadding">';
    $text.='<tr valign="middle" class="nobordernopadding">';

    // Color of agenda
    $text.='<td style="padding-left: 4px; padding-right: 4px" nowrap="nowrap">';
    $box ='<!-- Box color '.$selected.' -->';
    $box.='<table style="border-collapse: collapse; margin:0px; padding: 0px; border: 1px solid #888888;';
    if ($addcolor) $box.=' background: #'.(preg_replace('/#/','','#FFFFFF')).';';
    $box.='" width="12" height="10">';
    $box.='<tr class="nocellnopadd"><td></td></tr>';    // To show box
    $box.='</table>';
    $text.=$box;
    $text.='</td>';

    // Name of agenda
    $text.='<td>';
    $text.='<a class="vsmenu" href="'.$link.'"><strong>'.$langs->trans("All").'</strong></a>';
    $text.='</td></tr>';

    $text.='</table>';

    $finaltext=$text.$finaltext;
}

print $finaltext;

dol_fiche_end();


// Define parameters
$bgcolor='FFFFFF';
$color_file = DOL_DOCUMENT_ROOT."/theme/".$conf->theme."/graph-color.php";
if (is_readable($color_file))
{
	include_once($color_file);
	if (! empty($theme_bgcolor)) $bgcolor=dechex($theme_bgcolor[0]).dechex($theme_bgcolor[1]).dechex($theme_bgcolor[2]);
}

$frame ='<iframe src="http://www.google.com/calendar/embed?';
$frame.='showTitle=0';
$frame.='&amp;height=600';
// Define first day of week (wkst=1 for sunday, wkst=2 for monday, ...)
//var_dump($conf->global->MAIN_START_WEEK);
$frame.='&amp;wkst='.($conf->global->MAIN_START_WEEK+1);
$frame.='&amp;bgcolor=%23'.$bgcolor;


$i=1;
while ($i <= $MAXAGENDA)
{
	//$src  =array('eldy10%40gmail.com','5car0sbosqr5dt08157ro5vkuuiv8oeo%40import.calendar.google.com','french__fr%40holiday.calendar.google.com','sjm1hvsrbqklca6ju6hlcj1vdgvatuh0%40import.calendar.google.com');
	//$color=array('A32929','7A367A','B1365F','0D7813');

	$paramname='GOOGLE_AGENDA_NAME'.$i;
	$paramsrc='GOOGLE_AGENDA_SRC'.$i;
	$paramcolor='GOOGLE_AGENDA_COLOR'.$i;
	if (! empty($conf->global->$paramname))
	{
		if (isset($_GET["nocal"]))
		{
			if ($_GET["nocal"] == $i)
			{
				$frame.='&amp;src='.urlencode($conf->global->$paramsrc);
				$frame.='&amp;color='.urlencode('#'.preg_replace('/#/','',$conf->global->$paramcolor));
			}
		}
		else
		{
			$frame.='&amp;src='.urlencode($conf->global->$paramsrc);
			$frame.='&amp;color='.urlencode('#'.preg_replace('/#/','',$conf->global->$paramcolor));
		}
	}

	$i++;
}

// Add number of weeks (only if first day is monday)
if ($conf->global->MAIN_START_WEEK == 1)
{
	$frame.='&amp;src='.urlencode('e_2_fr#weeknum@group.v.calendar.google.com');
}

$frame.='&amp;ctz='.urlencode($conf->global->GOOGLE_AGENDA_TIMEZONE);
$frame.='" style=" border-width:0 " ';
$frame.='width="800" ';
$frame.='height="600" ';
$frame.='frameborder="0" scrolling="no">';
$frame.='</iframe>';

print $frame;

// End of page
$db->close();

llxFooter('$Date: 2011/05/16 17:25:56 $ - $Revision: 1.6 $');
?>
