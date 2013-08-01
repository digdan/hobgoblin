<div class="row">
	<div class="span6">
		<form id="imageform" method="post" enctype="multipart/form-data" action="/book/cover/<?= dechex($book->id);?>">
			Cover Image <a href="#" rel="tooltip" data-original-title="600x800 jpeg image"><i class="icon-question-sign"></i></a>
			<div id="bookCover" class="book-cover-full" onClick="$('#photoimg').click();">
				<IMG SRC="/book/cover/<?= dechex($book->id) ?>.jpg"/>
			</div>
			<input type="file" class="hide" name="photoimg" id="photoimg" onchange="$(this.form).submit();"/>
			<div id="coverReason"></div>
		</form>





		<div class="span6">

		<form id="book" method='post' action='/book/edit/<?= dechex($book->id) ?>'>
		<input type="hidden" name="action" id="action">
		<div class="row">
			<label class="control-label" for="visible">Title <a href="#" rel="tooltip" data-original-title="Title of your book"><i class="icon-question-sign"></i></a></label>
			<input type="text" class="input-block-level" id="title" name="title" data-counter="counter-title" rel="popover" value="<?=$book->title;?>" data-original-title="Title" maxlength="128">
		</div>
			<div id="counter-title" class="pull-right muted"></div>
		<div class="row">
			<label class="control-label" for="visible">Visibility <a href="#" rel="tooltip" data-original-title="Your books visibility and availability"><i class="icon-question-sign"></i></a></label>
			<select name="visible" id="visible">
				<option value="open" <?= (($book->visible == "open") ? "SELECTED" : "")?> ><i class="icon-eye-open"></i>Open & Published</option>
				<option value="hide" <?= (($book->visible == "hide") ? "SELECTED" : "")?> ><i class="icon-eye-open"></i>Open & Hidden</option>
				<option value="closed" <?= (($book->visible == "close") ? "SELECTED" : "")?> ><i class="icon-eye-open"></i>Closed & Hidden</option>
			</select>
		</div>
		<div class="row">

		</div>
		<div class="row">
			Stats
		</div>
		<div class="row">
			<label class="control-label" for="summary">Summary <a href="#" rel="tooltip" data-original-title="A quick summmary to pitch your book"><i class="icon-question-sign"></i></a></label>
			<textarea class="input-block-level" data-counter="counter-summary" name="summary" id="summary" rows="6" maxlength="200"><?= $book->summary; ?></textarea>
			<div id="counter-summary" class="pull-right muted"></div>
		</div>
		<div class="row">
			<label class="control-label" for="description">Description <a href="#" rel="tooltip" data-original-title="Full description of your book"><i class="icon-question-sign"></i></a></label>
			<textarea class="input-block-level" data-counter="counter-description" name="description" id="description" rows="12" maxlength="600"><?= $book->description; ?></textarea>
			<div id="counter-description" class="pull-right muted"></div>
		</div>
		<div class="row form-actions">
			<div class="btn-group pull-left">
				<a class="btn btn-warning dropdown-toggle " data-toggle="dropdown" href="#"> Administrate <span class="caret"></span> </a>
				<ul class="dropdown-menu">
					<li><a href="#" class="confirm"><i class="icon-remove-circle"></i> Delete Book</a></li>
				</ul>
			</div>
			<button type="submit" class="btn btn-success pull-right" id="update" rel="tooltip" title="Update Book">Update Book</button>
		</div>
		<div class="row">
			<div id="reason"><?= html_entity_decode($reason); ?></div>
		</div>
		</form>

		</div>


		</div>

    <div class="row">
		<div class="span6">
			<fieldset>
			<legend>Nodes</legend>
			<table class="table table-striped table-bordered datatable">
				<thead>
					<tr>
						<th>Node</th>
						<th>Title</th>
						<th>Status</th>
						<th>View/Downloads/Reads</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
<?
	if (count($nodes) > 0) {
		foreach($nodes as  $k=>$v) {
			echo "<tr>";
			echo "\t<td>{$v->id}</td>\n";
			echo "\t<td>{$v->title}</td>\n";
			echo "\t<td>{$v->status}</td>\n";
			echo "\t<td>{$v->stats}</td>\n";
			echo "\t<td>{$v->actions}</td>\n";
			echo "</tr>";
		}
	}
?>
				</tbody>
			</table>

			<A href="/node/create/<?= dechex($book->id);?>" class="btn btn-primary pull-right">Create New Node</A>
	</div>
</div>
<script type="text/javascript">
$(document).ready(function() {
	limitCount('title');
	limitCount('summary');
	limitCount('description');

	$(".confirm").on("click",function() {
		if (confirm('Are you sure you wish to PERMANENTLY delete this book?')) {
			$('#action').val('delete');
			$('#book').submit();
		} else {
			return false;
		}
	});

	$("#imageform").ajaxForm({
		success: function( data ) {
			$('#bookCover').html('<IMG SRC="/book/cover/<?= dechex($book->id)?>.jpg?date=' + (new Date).getTime() + '"/>');
		},
		beforeSubmit: function() {
			$(this).addClass('loading');
			$('#bookCover').html('<div id="progressOverlay"><div class="progress progress-striped"><div class="bar" id="progressBar" style="width: 0%;">0%</div></div></div>');
		},
		uploadProgress : function( event, position, total, percentComplete ) {
			if (percentComplete == 100) {
				$('#progressBar').css('width',percentComplete+'%').html('Processing...');
			} else {
				$('#progressBar').css('width',percentComplete+'%').html(percentComplete+'%');
			}
		}

	});
});

$(".datatable").dataTable({
	"sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
	"sPaginationType": "bootstrap",
	"sScrollY": "200px",
	"bPaginate": false,
	"bScrollCollapse": true
});
</script>