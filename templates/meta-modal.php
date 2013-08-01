<div id="meta" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
	<form method="post" action="/content/meta" id="metaForm">
		<input type="hidden" name="page_name" value="<?= $active["current"]; ?>">
		<input type="hidden" name="page_source" value="<?= $_SERVER["REQUEST_URI"]; ?>">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3>Meta Information</h3>
		</div>
		<div class="modal-body">
			<fieldset>
				<div class="control-group">
					<label class="control-label" for="email">Title</label>
					<div class="controls">
						<input type="text" class="input-large" id="title" name="title" rel="popover" value="<?= $meta["title"];?>" data-original-title="Title">
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="email">Keywords</label>
					<div class="controls">
						<input type="text" class="input-large" id="keywords" name="keywords" rel="popover" value="<?= $meta["keywords"];?>" data-original-title="Keywords">
					</div>
				</div>

				<div class="control-group">
					<label class="control-label" for="email">Description</label>
					<div class="controls">
						<input type="text" class="input-large" id="description" name="description" rel="popover" value="<?= $meta["description"];?>" data-original-title="Description">
					</div>
				</div>
			</fieldset>
		</div>

		<div class="modal-footer">
			<button class="btn btn-primary" id="meta-save">Update</button>
			<button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
		</div>
	</form>
</div>