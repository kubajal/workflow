// Cancel click event
jQuery( document ).ready(function() {
	
	jQuery('#processItems').on('change',(function(evt){

		itemId=jQuery(this).val();

	    processItemClicked(evt,itemId);
	})); 

	jQuery('.error').addClass('ui-icon ui-icon-alert');
	var objs=jQuery('.validtionError');
	jQuery(objs).addClass('ui-icon ui-icon-alert');
});



jQuery(function() {
jQuery( "#diagram" ).resizable({
  alsoResize: "#workArea",
	  minWidth: 1200,
      minHeight: 300
});


jQuery( "#workArea" ).resizable({
	  minWidth: 1200,
      minHeight: 300
  });
});

jQuery(function() {
    jQuery( "#tabs" ).tabs();
  });

jQuery(function() {
	jQuery( "#accordion" ).accordion({
    heightStyle: "fill"
  });
});
jQuery(function() {
	jQuery( "#accordion-resizer" ).resizable({
    minHeight: 140,
    minWidth: 200,
    resize: function() {
    	jQuery( "#accordion" ).accordion( "refresh" );
    }
  });
});

var counter=0;
function processItemClicked(evt,id)
{
    try
    {
	if(id==null)
		return;
	
	
	var ajax=true;
 	var el=jQuery('#itemDetails');
 	var file;
 	var caseId;
 	var id;

    jQuery("#ItemsList").val(id);
 	
/* 	jQuery( "#tabs" ).tabs( "option", "active", 1); */
    
    counter++;
 	
 	el.html("loading item details ..."+counter);
 	
 
 	file=getParameterByName('file');
 	
 	var baseUrl=window.location.href.split('?')[0];
 	url= baseUrl+"?action=itemDetails&file="+file+"&item="+id;
 	if (File==null || file=="")
 		{
 		caseId=getParameterByName('caseId');
 		url=baseUrl+"?action=itemDetails&caseId="+caseId+"&item="+id;
 		}
	
 	if (ajax==false)
 	{
 	
 		el.html("<iframe src='"+url+"' width='100%' height='240px' />");
 	}
 	else
 	{
 	var data = {
 				'action': 'omniitemDetails',
 				'file': file,
 				'caseId': caseId,
 				'item': id
 			};
 	
 	if (ajax_object!=null)
 		{
 		url=ajax_object.ajax_url;
 		}
	jQuery.post(url, data, function(response) {
	 	el.html(response);

		}); 	 	
 	
/* 	jQuery.post({
 		url: ajaxurl ,  $data,
 	  beforeSend: function( xhr ) {
 	    xhr.overrideMimeType( "text/plain; charset=x-user-defined" );
 	  }
 	})
 	 .done(function( data ) {
		 	el.html(data);
 	    }
 	  ); */
	

 	 }
    }
   catch(exc)
   {
	   alert(exc);
   }
 	
}
function getParameterByName(name) {
    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
        results = regex.exec(location.search);
    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
}

