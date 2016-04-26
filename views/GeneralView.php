<?php
namespace OmniFlow

{

class GeneralView extends Views
{
public function Welcome2()
{
    
}
    
public function Welcome()
{
    ?>
For help <a href='http://workflow.omnibuilder.com/' target='_blank'>please visit our website</a>
<?php

    return;
    $this->styles();
?>
<!-- Tabs -->
<div id="tabs" style="float:left;width: 100%;max-height:100vh;margin: 5px; margin-left: 5px;">
	<ul>
		<li><a href="#Introduction-tab">Introduction</a></li>
		<li><a href="#Processes-tab">Processes</a></li>
		<li><a href="#Admin-tab">Administration</a></li>
		<li><a href="#help-tab">Documentation</a></li>
	</ul>
    <div id="Introduction-tab">
        <iframe style='width:100%;height:80vh;' 
                src='http://workflow.omnibuilder.com/quick-tour/overview/'></iframe>
    </div>
</div>
    

<script>

jQuery( "#tabs" ).tabs()
    .addClass('ui-tabs-vertical ui-helper-clearfix');
jQuery( ".accordion" ).accordion({heightStyle: "content" , collapsible: true });
</script>
 <?php
    
}
public function styles()
{
?>
<style>
.ui-tabs.ui-tabs-vertical {
    padding: 0;
/*    width: 42em; */
}
.ui-tabs.ui-tabs-vertical .ui-widget-header {
    border: none;
}
.ui-tabs.ui-tabs-vertical .ui-tabs-nav {
    float: left;
    width: 10em;
    background: #CCC;
    border-radius: 4px 0 0 4px;
    border-right: 1px solid gray;
}
.ui-tabs.ui-tabs-vertical .ui-tabs-nav li {
    clear: left;
    width: 100%;
    margin: 0.2em 0;
    border: 1px solid gray;
    border-width: 1px 0 1px 1px;
    border-radius: 4px 0 0 4px;
    overflow: hidden;
    position: relative;
    right: -2px;
    z-index: 2;
}
.ui-tabs.ui-tabs-vertical .ui-tabs-nav li a {
    display: block;
    width: 100%;
    padding: 0.6em 1em;
}
.ui-tabs.ui-tabs-vertical .ui-tabs-nav li a:hover {
    cursor: pointer;
}
.ui-tabs.ui-tabs-vertical .ui-tabs-nav li.ui-tabs-active {
    margin-bottom: 0.2em;
    padding-bottom: 0;
    border-right: 1px solid white;
}
.ui-tabs.ui-tabs-vertical .ui-tabs-nav li:last-child {
    margin-bottom: 10px;
}
.ui-tabs.ui-tabs-vertical .ui-tabs-panel {
    float: left;
/*    width: 28em;*/
    border-left: 1px solid gray;
    border-radius: 0;
    position: relative;
    left: -1px;
}
    
</style>    
<?php
}
function Help()
{
    ?>
For help <a href='http://workflow.omnibuilder.com/' target='_blank'>please visit our website</a>
<?php
        return;
    $this->styles();
?>
<!-- Tabs -->
<div id="tabs" style="float:left;width: 100%;margin: 5px; margin-left: 5px;">
	<ul>
		<li><a href="#settings-tab">Settings</a></li>
		<li><a href="#data-tab">Data</a></li>
		<li><a href="#currentRecord-tab">Record Detail</a></li>
		<li><a href="#features-tab">Script Language</a></li>
	</ul>
    <div id="features-tab">
<!------------------------------- -->
            <!-- Accordion -->
            <h2 class="demoHeaders">Script Language</h2>
            <div id="accordion" class='accordion'>
                        <?php  include ("http://workflow.omnnibuilder.com/help/ScriptLanguage.html");?>
            </div>
       </div>
<!------------------------ -->
<div id="settings-tab">
    Settings
</div>
        
<div id="data-tab"></div>

<div id='currentRecord-tab'>
 
</div>


</div>
    
    
</div>

<script>

jQuery( "#tabs" ).tabs()
    .addClass('ui-tabs-vertical ui-helper-clearfix');
jQuery( ".accordion" ).accordion({heightStyle: "content" , collapsible: true });
</script>
 <?php
        
}
}	// end of class

}	// end of namespace

?>