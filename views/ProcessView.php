<?php
namespace OmniFlow;

class ProcessView extends Views
{
public function ViewProcess(BPMN\Process $proc)
{
    
        $arr['itemsDescription']=$proc->Describe();        
        $json=$json=json_encode($arr);
	?>

<script>
        inDesignMode=false;
        inViewMode=true;
        var modelTitle='<?php echo $proc->title; ?>';
        jsonData=<?php echo $json; ?>
        
	jQuery( document ).ready(function() {
/*
        jQuery('g[data-element-id]').click(function (event) { 
            alert(this.attributes['data-element-id'].value); 
        });
    
        jQuery('g[data-element-id]').attr('data-toggle', 'popover');
        jQuery('g[data-element-id]').attr('data-content', 'testing');


        jQuery('[data-toggle="popover"]').popover({
            trigger: 'hover',
                'placement': 'top'


        });	
         */
		
	});		

</script>
    <h2>
        Process View for :<?php echo $proc->processName;?>
    </h2>
	<div id="MainLayout" class='mainLayout'>
	<!-- js will embed layout here -->
            <div id='diagramContents' style="position: relative; width: 100%;">
            <?php 
            $imageFile =$proc->getImageFileName();
            $decors=Array();
            
            foreach($proc->items as $item)
            {
               if ($item->seq !==null)
                $decors[]=Array($item,$item->seq,'black');
            }
            SVGHandler::displayDiagram($proc,$decors);

//           echo '</div>';
            
            
            
            ?>
            </div>
            <!-- end of diagram -->
<?php
}

public function DesignProcess(BPMN\Process $proc)
{
	?>
<script>
        var modelTitle='<?php echo $proc->title; ?>';
	jQuery( document ).ready(function() {
			BuildPage();
			getJson();
			initFields();
                        inDesignMode=true;
                        jQuery('#diagramContents').parent().css('overflow-y','auto');                        
                       
	});		
</script>
	<div id="MainLayout" class='mainLayout'>
	<!-- js will embed layout here -->
            <div id='diagramContents' style="position: relative; width: 100%;">
            <?php 
            $imageFile =$proc->getImageFileName();
//            $imageFile = 'processes/'.str_replace(".bpmn", ".svg",$file);

            echo "<div>";// style='overflow:scroll;height:400px;'>";

            SVGHandler::displayDiagram($proc,array());

           echo '</div>';
            
            ?>
            </div>
            <!-- end of diagram -->
            <div id="proessItems" style="position: relative; width: 100%;">
                          <div id="ItemsList">
                            </div> <!-- end of Items list -->
                                    <div id="itemDetails">
                                    </div>
            </div>
            <div id="process-workArea">

                    <div id="DataModel"></div>
            </div>
	</div>
<?php

}

}
