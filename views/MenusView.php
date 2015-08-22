<?php
namespace OmniFlow
{
/*
 * 

 */
class MenusView
{
    static function displayMenus($localMenus=array())
{
    $user=  Context::getUser();

    $canDesign=false;
    
    if ($user->isLoggedIn())
        $canDesign=  $user->can('design');
    else {
    
//        return;
    }
    if (count($localMenus)>0)
    {
?>
<div id='omni_menus' style="float:left;width: 60%;
  padding-left: 40px; 
  background-color: rgb(238, 220, 239);"> </div>
<div id='omni_menus_local' style="float:right;width: 40%;
  padding-left: 40px; 
  background-color: rgb(238, 220, 239);"> </div>
</div>
<?php
    }
    else
    {
?>
<div id='omni_menus' style="float:left;width: 100%;
  padding-left: 40px; 
  background-color: rgb(238, 220, 239);"> </div>
<?php
        
    }
        
?>
<!-- MenusView.php:displayMenus -->

<div style="border: 1px solid #000000;overflow: auto;width: 100%">

<script>
var omniMenus = new dhtmlXMenuObject("omni_menus");

<?php if ($canDesign) { ?>
	omniMenus.addNewSibling(null, "Admin", "Admin", false); 
            omniMenus.addNewChild("Admin", 0, "process.list", "List Processes", false); 
            omniMenus.addNewChild("Admin", 1, "admin.listEvents", "List Events", false); 
            omniMenus.addNewChild("Admin", 2, "admin.resetCaseData", "Reset Case Data", false); 
<?php } ?>
	omniMenus.addNewSibling(null, "Cases", "Cases", false); 
            omniMenus.addNewChild("Cases", 0, "case.list", "List", false); 
            omniMenus.addNewChild("Cases", 1, "case.query", "Query", false); 
	omniMenus.addNewSibling(null, "Tasks", "Tasks", false); 
            omniMenus.addNewChild("Tasks", 0, "task.list", "List", false); 
            omniMenus.addNewChild("Tasks", 1, "task.query", "Query", false); 
            omniMenus.addNewChild("Tasks", 2, "task.dashboard", "Dashboard", false); 
	omniMenus.addNewSibling(null, "process.startList", "Start...", false); 
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