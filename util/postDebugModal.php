<?php
	if ($_POST) {
?>
<!-- sample modal content -->
	<div id="postDebug" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<h3>Submitted by Form</h3>
		</div>
		<div class="modal-body">
			<h4>The following was submitted by the previous form:</h4>
			<p>
			<pre>
<?php var_dump($_POST); ?>
			</pre>
			</p>
		</div>
		<div class="modal-footer">
			<a href="#" class="btn" data-dismiss="modal" >Close</a>
		</div>
	</div>
	<script src="res/bootstrap/js/jquery.js"></script>
	<script src="res/bootstrap/js/bootstrap.js"></script>
	<script>
		$('#postDebug').modal('show')
	</script>
<?php
	}
?>