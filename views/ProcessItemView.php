<?php
namespace OmniFlow
{

class ProcessItemView extends Views
{
	
public static function ShowItemDetails(ProcessItem $item,$formAction=null)
{

	$props=MetaProperty::getActivityProperties();
	if ($formAction==null)
		$editable=false;
	else
		$editable=true;

	if ($editable)
		echo "<form name='ajaxform' id='ajaxform' action='$formAction' method='post'>";

	$className=Helper::getClassName($item);

	?>
<script>	
jQuery(function() {
    jQuery( "#vtabs" ).tabs();
  });
  </script>
   
  <div id="vtabs">
  <ul>
    <li><a href="#vtabs-1">General</a></li>
    <li><a href="#vtabs-2">Roles</a></li>
    <li><a href="#vtabs-3">Action</a></li>
    <li><a href="#vtabs-4">Timer</a></li>
    <li><a href="#vtabs-5">Message</a></li>
  </ul>
	  <div id='vtabs-1'>	
		<table><tr><td colspan="4">
		<?php echo $item->describe(); ?>
			</td></tr><tr>
		
		<?php 	self::field($props['name'],$item,$editable);
				self::field($props['id'],$item,$editable);
			if ($className=='Flow')
			{
				self::newLine();
				self::field($props['condition'],$item,$editable);
			}
			elseif ($item->hasTimer)
			{
				self::newLine();
				self::field($props['timerType'],$item,$editable);
				self::field($props['timer'],$item,$editable);
				self::field($props['timerRepeat'],$item,$editable);
			}
			elseif ($item->hasMessage)
			{
				self::newLine();
				self::field($props['message'],$item,$editable);
		
			}
				
 ?>
		
		</tr></table>
	</div>
	
    <div id='vtabs-2'><xtable><tr><td></td>	
		<?php self::field($props['actor'],$item,$editable);?>
		</tr></table>
	</div>

	<div id="vtabs-3"><xtable><tr><td></td>	
		<?php	self::field($props['actionScript'],$item,$editable);
			self::field($props['actionType'],$item,$editable); ?>
		</tr></table>
	</div>

	<div id="vtabs-4"><table><tr><td></td>
		<?php	
			self::field($props['timerType'],$item,$editable);
			self::field($props['timer'],$item,$editable);
			self::field($props['timerRepeat'],$item,$editable);?>
	
		</tr></table>
	</div>
			
    <div id="vtabs-5">
    
	    <txable><tr><td></td>
	    
		</tr><tr>
		
		</tr></table>
	</div>
	</div>
	</div> <!-- end tabs -->
	<?php
	
	if ($formAction!=null)
	{
		echo '<input type="submit" /></form>';
	}
	echo '<div class="vaidationErrors">';
	ValidationRule::ValidateItem($item);
	
}
static function newLine()
{
	echo '</tr><tr>';
}
static function field($prop,$item,$editable=true)
{
	if ($prop==null)
		return;
	if ($prop->editStyle>0)
	{
			
		$pn=$prop->name;
		$title=$prop->title;
		$val=$item->$pn;
		$colspan="";
		$valInput=$val;
		if ($prop->editStyle==MetaProperty::EditText && $editable)
		{
			$valInput="<textArea name='$pn' cols='60' rows='5'>$val</textArea>";
			$colspan="colspan='3'";
		}
		if ($prop->editStyle==MetaProperty::Editable && $editable)
			$valInput="<input name='$pn' value='$val' />";
		if ($prop->editStyle==MetaProperty::EditDropDown && $editable)
		{
			$valInput="
			<select name='$pn' id='pn'>";
			foreach($prop->editValues as $sval)
			{
				$sel="";
				if ($val==$sval)
					$sel="selected";
					
				$valInput.="<option value='$sval' $sel>$sval</option>";
			}
			$valInput.="</select>";
		}
		echo "<td>$title</td><td $colspan>$valInput</td>";
	}

}

}
}