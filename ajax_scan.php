<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>

<h2>Full Scan & Resize</h2>
<p>Use this tool to compress all existing pictures of your blog, using the previous configuration. Don't close the page while the script is running.</p>
<div style="background: #E5E5E5; font-family: 'Trebuchet MS'; width: 80%; margin: 0px auto;">
	<!-- table -->
	<table id="results" style="width: 100%; border: 1px solid #FF9900; border-spacing: 0; border-padding: 0">
	<tr style="background: #262626; color:#FF9900">
		<th>File</th><th>Status</th><th>Comments</th>
	</tr>
	</table>
	<!-- loader -->
	<br>
	<div id="loading_txt" style="text-align: center">
		<a href="javascript:startFullScan();">Click here to start scanning upload directory.</a>
	</div>
	<div id="loading_img" style="width: 100%;height: 32px;background-image:url('<?php echo plugin_dir_url(__FILE__); ?>loader.gif');background-repeat:no-repeat;background-position:top;display:none">
	</div>
</div>

<script>
	var nbCalls = 0;
	var startTime = 0;
	
	function startFullScan()
	{
		startTime = new Date().getTime(); 
		callResizeScript();
	}
	
	function callResizeScript()
	{
		nbCalls++;
		updateLoading(nbCalls);
		
		var formAction = '<?php echo plugin_dir_url(__FILE__); ?>ajax_scan_service.php';
		$.ajax({
			url: formAction,
			type: "POST",
			data: {'vv_callAjax':'true'},
			timeout : 35000,
			success: function(data, textStatus, jqXHR){
				try 
				{
					var jsonData = $.parseJSON(data);
					if(jsonData.files == null) return;
					
					var relaunchScript = false;
					for(var i=0; i<jsonData.files.length; i++)
					{				
						var file = jsonData.files[i];
						var line = '<tr style="background: #FFFFFF; color:#000000">';
						line +='<td>'+file.name+'</td>';
						line +='<td>'+file.status+'</td>';
						line += '<td>'+file.comments+'</td>';
						line += '</tr>';
						$("#results").append(line);
						
						if(file.status == 'Success')
						{
							relaunchScript = true;
						}
					}
					if(relaunchScript == true) 
					{
						callResizeScript();
					}
				}catch(e){
					showError(data);
				}
				endLoading();
			},
			error: function(jqXHR, textStatus, errorThrown){
				showError(textStatus + '<br>' + errorThrown);
			}
		});
	}
	
	function updateLoading()
	{
		$("#loading_img").show();
		$("#loading_txt").html("Loading... (loop "+nbCalls+")");
	}
	
	function endLoading()
	{
		var totalTime = (new Date().getTime() - startTime) / 1000;
		$("#loading_img").hide();
		$("#loading_txt").html("Script done with "+nbCalls+" loop(s) in "+totalTime+" seconds");
	}
	
	function showError(error)
	{
		$("#loading_img").hide();
		$("#loading_txt").html(error);
	}
</script>