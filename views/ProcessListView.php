<?php
namespace OmniFlow

{

class ProcessListView extends Views
{
    public function ProcessList($list,$actions)
    {
        echo '<div id="actionsList"><table width="100%">';
        
        foreach($actions as $action=>$title)
        {
            //$link=Helper::getUrl(array('action'=>$action,'processId'=>$id)); 
            $lnk=Helper::getUrl(array('action'=>$action)); 
            switch($title)
            {
                case 'Model':
                    $desc='Define the business model according to BPMN 2.0 standards using drag and drop';
                    break;
                case 'Design':
                    $desc='Define the design details and integration points';
                    break;
                case 'Test':
                    $desc='Simulate your process to verify your design';
                    break;
                case 'Export':
                    $desc='Export model definition to external files';
                    break;
                case 'Delete':
                    $desc='Remove the model from the repository';
                    break;
                
            }
    ?>
<tr> <td width='75px'>
        <a href="javascript:doAction('<?php echo $lnk; ?>');">
            <?php echo $title;?>
        </a></td> <td><?php echo $desc;?> </td>
</tr>        
    <?php    }
    ?>
        </table>
    </div>

        
    <div id='MainLayout' class='mainLayout'></div>
    <script>
        
	dxImgPath=omni_base_url+'/dhtmlx/codebase/imgs/';
	window.skin = "skyblue"; // for tree image_path
			dhtmlx.image_path=dxImgPath;

	main_layout = new dhtmlXLayoutObject('MainLayout', '2U');
//        main_layout.setAutoSize("a", "a;b");
	var procGrid = main_layout.cells('a');
        procGrid.setWidth(600);
	procGrid.setText('Processes');
	procGrid.setCollapsedText('Processes');
	var details= main_layout.cells('b');
        details.setText("Actions");
        main_layout.cells('b').attachObject('actionsList');
        
    function doAction(action)
    {
        var rowId=procGrid.getSelectedRowId();
        url=action+'&processId='+rowId;
        window.location=url;        
    }
    </script>
<?php
        
    $rows=array();
        foreach($list as $proc)
        {
            $row=array();
                $name=$proc['processName'];
                $title=$proc['title'];
                $row['name']=$name;
                $row['title']=$title;
                $id=$proc['processId'];
                $row['id']=$id;
/*
                foreach($actions as $action=>$desc)
                {
		$link=Helper::getUrl(array('action'=>$action,'processId'=>$id)); 
                $row[$action]=$desc.'^'.$link.'^_self';
                } */
            $rows[]=$row;

        }
        $cols=array();
        $titles=array();
        $cols[]='name,title';
        $titles[]='Name,Title';
        $types[]='ro,ro';
        /*
            foreach($actions as $action=>$desc)
            {
            $cols[]=$action;
            $titles[]=$desc;
            $types[]='link';
            } */
        $widths=Array();
        $this->displayGrid('procGrid',$rows,$cols,$titles,$types,$widths,
        "width:1000px;min-height:300px;height=60%",
                'procGrid');        
}

}	// end of class

}	// end of namespace
?>