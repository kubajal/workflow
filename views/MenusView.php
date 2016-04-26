<?php
namespace OmniFlow
{
/*
 * 

 */
class MenusView
{
    static function displayMenus($localMenus=array(),$maximize=true)
{
    $user=  Context::getUser();
    self::getJavaScript($maximize);
    self::getMenusBootstrap($user,$localMenus); 
//    self::getMenus($user,$localMenus); 
}
public static function getJavaScript($maximize)
{

?>
<script>
var originalParent;    
jQuery( document ).ready(function() {


    jQuery("#menu-bar").dblclick(function(){
            maxSwitch();
        });

    originalParent=jQuery('#omni_page').parent();

    jQuery("#maxButton").click(function(){
            maxSwitch();
        });    
<?php if((!Context::inAdmin()) && ($maximize===true)) { ?>
        maxSwitch();
<?php    } ?>
});    
function maxSwitch()
{
            var parent=jQuery('#omni_page').parent();
            if (parent.is('body'))
            {
                var element = jQuery('#omni_page').detach();
                jQuery(originalParent).append(element);
            }
            else
            {
                var element = jQuery('#omni_page').detach();
                jQuery('body').append(element);
            }
                
            jQuery("#omni_page").toggleClass("maxScreen");
            jQuery("#page").toggleClass("hide");
        
            var op=jQuery("omni_page");

            if (typeof main_layout !== 'undefined') {
                    main_layout.setSizes();
                }
    
}
</script>
<?php
}
public static function buildMenus($user,$localMenus)
{
    
    $menu=Array();
    
        //  action, Title , current ,children
    if ($user->can('design') || $user->can('view_design') || $user->can('model') || $user->can('view_model')) { 
	$menus[]=Array("process.show", "Processes", false); 
    } 
    
	$menus[]=Array("task.dashboard", "Dashboard", false);

        $menus[]=Array("case.list", "Cases", false ); /*, Array(
            Array( "case.list", "List", false),
            Array("case.query", "Query", false))); */
    
    if ($user->can('admin')) {

	$menus[]=Array("Admin", "Admin", false, Array(
                Array("admin.listEvents", "List Events", false),
                Array("admin.resetCaseData", "Reset Case Data", false),
                Array("admin.installDB", "Reset All Data", false)));
        
        $menus[]=Array("#help", "Help", false);
    } 
    return $menus;
}
public static function getMenus($user,$localMenus)
{
?>
<div id='menu-bar'>
    <div style="width:40px;float:left">
                <image  id='maxButton' 
                        src="<?php echo Context::getInstance()->omniBaseURL;?>/images/max-screen.png" />
    </div>
<?php
    $canDesign=false;
    
    if ($user->isLoggedIn())
        $canDesign=  $user->can('design');
    else {
    
//        return;
    }
    if (count($localMenus)>0)
    {
?>
<div id='omni_menus'> </div>
<div id='omni_menus_local'> </div>
</div>
<?php
    }
    else
    {
?>
<div id='omni_menus'> </div>
<?php
        
    }
        
?>
</div>

<div style="border: 1px solid #000000;overflow: auto;width: 100%">

<script>
var omniMenus = new dhtmlXMenuObject("omni_menus");

<?php if ($canDesign) { ?>
	omniMenus.addNewSibling(null, "general.help", "Help", false); 
	omniMenus.addNewSibling(null, "Admin", "Admin", false); 
//            omniMenus.addNewChild("Admin", 0, "process.list", "List Processes", false); 
            omniMenus.addNewChild("Admin", 1, "admin.listEvents", "List Events", false); 
            omniMenus.addNewChild("Admin", 2, "admin.resetCaseData", "Reset Case Data", false); 
            omniMenus.addNewChild("Admin", 2, "admin.installDB", "Reset All Data", false); 
<?php } ?>
	omniMenus.addNewSibling(null, "Cases", "Cases", false); 
            omniMenus.addNewChild("Cases", 0, "case.list", "List", false); 
            omniMenus.addNewChild("Cases", 1, "case.query", "Query", false); 
	omniMenus.addNewSibling(null, "task.dashboard", "Dashboard", false); 
//            omniMenus.addNewChild("Tasks", 0, "task.list", "List", false); 
//            omniMenus.addNewChild("Tasks", 1, "task.query", "Query", false); 
//            omniMenus.addNewChild("Tasks", 2, "task.dashboard", "Dashboard", false); 
//	omniMenus.addNewSibling(null, "process.startList", "Start...", false); 
<?php if ($canDesign) { ?>
	omniMenus.addNewSibling(null, "process.show", "Processes", false); 
//	omniMenus.addNewSibling(null, "Model", "Model", false); 
//            omniMenus.addNewChild("Model", 0, "process.import", "Import..", false); 
<?php } ?>
            //     omniMenus.addNewSeparator("Admin","Menus");	
	omniMenus.attachEvent('onClick', function(id,zoneId,cas)
	{
            if (id.indexOf('local.') > -1)
            {
                return;
            }
            var inAdminMode=false;
            var seperator='?';

                if (typeof omni_admin_page !== 'undefined') {
                    inAdminMode=omni_admin_page;
                    }
                if (inAdminMode)
                    seperator='&';

                var url=window.location.href.split(seperator)[0];
                url=url+seperator+"action="+id;
                 window.location=url;
            return;
	});
<?php
    if (count($localMenus)>0)
    {
        echo 'var omniMenusLocal = new dhtmlXMenuObject("omni_menus_local");';

        $scr="
	omniMenusLocal.attachEvent('onClick', function(id,zoneId,cas)
	{";
        
        foreach($localMenus as $menu)
        {

            $id=$menu[0];
            $title=$menu[1];
            $funct=$menu[2];
            if ($funct!='')
            {
                $scr.="if (id=='$id') { $funct;return; }";
            }
            echo 'omniMenusLocal.addNewSibling(null, "'.$id.'", "'.$title.'", false); ';
            
        }
            $scr.="
            var inAdminMode=false;
            var seperator='?';

                if (typeof omni_admin_page !== 'undefined') {
                    inAdminMode=omni_admin_page;
                    }
                if (inAdminMode)
                    seperator='&';

                var url=window.location.href.split(seperator)[0];
                url=url+seperator+'action='+id;
                 window.location=url;
            
            return;
	}); ";
                
        echo $scr;              
        
    }
?>
</script>
<?php
}
public static function getMenusBootstrap($user,$localMenus)
{
    $menus=self::buildMenus($user, $localMenus);
?>
<!-- MenusView.php:displayMenus -->

<!-- bootstrap menus --->
    
<nav role="navigation" class="navbar navbar-default">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
        <button type="button" data-target="#navbarCollapse" data-toggle="collapse" class="navbar-toggle">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
        <a href="#" class="navbar-brand"><img 
                src="<?php echo Context::getInstance()->omniBaseURL;?>/images/omniworkflow_icon.png" /></a>
    </div>
    <!-- Collection of nav links, forms, and other content for toggling -->
    <div id="navbarCollapse" class="collapse navbar-collapse">
        <ul class="nav navbar-nav">
            <?php
        //  action, Title , current ,children
            
            foreach($menus as $menu)
            {
                $class="";
                $subs=null;
                $action=$menu[0];
                $title=$menu[1];
                $current=$menu[2];
                if (isset($menu[3]))
                    $subs=$menu[3];
                
                if ($action=='#help') {
                    $href='http://workflow.omnibuilder.com/help';
                    echo "<li><a class='$class' href='$href' target='_blank'>$title</a></li>";
                    continue;
                }
                
                
                $href=Helper::getUrl(array("action"=>$action));

                
                if ($subs===null) {
                    echo "<li><a class='$class' href='$href'>$title</a></li>";
                } else {

                      echo "<li class='dropdown'>
                        <a data-toggle='dropdown' class='xdropdown-toggle' href='#'>$title <b class='caret'></b></a>
                        <ul role='menu' class='dropdown-menu'>";
                        foreach($subs as $sub) {

                            $action=$sub[0];
                            $href=Helper::getUrl(array("action"=>$action));
                            $title=$sub[1];

                             echo "<li><a href='$href'>$title</a></li>";
                        }
                        echo '
                            </ul>
                            </li>         ';           
                    }
            }
    echo '</ul>';
    $l=count($localMenus);
     {
        echo '<ul class="nav navbar-nav navbar-right">';
        $i=$l-1;
        while($i > -1)
        {
                $menu=$localMenus[$i];
                $id=$menu[0];
                $title=$menu[1];
                $funct=$menu[2];

                if ($funct!='')
                { 
                    $funct=str_replace('return;','',$funct);
                    $href="javascript:".$funct."";
                }
                else
                    $href=Helper::getUrl(array("action"=>$id));


                if ($funct=='')
                    $funct=$id;
                echo "<li><a href='$href'>$title</a></li>";
                $i--;
        }

        ?><li >
            <image  style='padding:7px;' id='maxButton' 
                        src="<?php echo Context::getInstance()->omniBaseURL;?>/images/max-screen.png" />
        </li>
        <?php
        echo '        </ul>';
    }
    echo '</div>
</nav> ';

?>
        <!--
<div id='menu-bar'>
    <div style="width:140px;float:left">
                <image  id='maxButton' 
                        src="<?php echo Context::getInstance()->omniBaseURL;?>/images/max-screen.png" />
    </div>
</div> -->
   <div class='clearfix' height=0;></div>
<?php
}
static function displayMenus2()
{
?>
<table width="100%">
<tr>
<td><a href="http://demo.bpmn.io/">Design</a></td>
<td><a href="<?php echo Helper::getUrl(array('action'=>'process.show')); ?>">Processes</a></td>
<td><a href="<?php echo Helper::getUrl(array('action'=>'process.list')); ?>">List Processes</a></td>
<td><a href="<?php echo Helper::getUrl(array('action'=>'case.list')); ?>">List Cases</a></td>
<td><a href="<?php echo Helper::getUrl(array('action'=>'listTasks'));?>">List Tasks</a></td>
<td><a href="<?php echo Helper::getUrl(array('action'=>'listEvents'));?>">List Events</a>
<td><a href="<?php echo Helper::getUrl(array('action'=>'listMessages'));?>">List Messages</a>
<td><a href="<?php echo Helper::getUrl(array('action'=>'setting'));?> ">Settings</a>
<td><a href="<?php echo Helper::getUrl(array('action'=>'help'));?>">Help</a>
</td>
</tr>
</table>
<?php
}
}
} 
?>