<div class="alert alert-block alert-<?=$messageType;?>">
	<a class="close" data-dismiss="alert" href="#">×</a>
	<?php if (isset($messageTopic) && !empty($messageTopic)) { ?><h4 class="alert-heading"><?=$messageTopic;?></h4><?php } ?>
	<?=$messageText;?>
</div>

<script type="text/javascript">
	$(".alert").alert()
</script>