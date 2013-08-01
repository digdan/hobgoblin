<div class="page-header"><H1>My Books</h1></div>
<div class="row">
	<div class="span6">
			<fieldset>
			<legend>My Books</legend>
	<?php if (isset($books)) { ?>
			<table class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>ID</th>
						<th>Title</th>
						<th>Visibility</th>
						<th>Statistics</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
				<?php
					$count = 0;
					foreach($books as $k=>$book) {
						$count++;
						$hex_id = dechex($book->id);
						echo "<tr>";
						echo "<td>{$count}</td>";
						echo "<td>{$book->title}</td>";
						echo "<td>{$book->visible}</td>";
						echo "<td>{$book->reads}</td>";
						echo "<td><a class=\"btn\" href=\"/book/edit/{$hex_id}\"><i class=\"icon-edit\"></i>&nbsp;Edit</a></td>";
						echo "</tr>";
					}
				?>
				</tbody>
			</table>
	<?php
			} else {
				echo "<p>No Books Found</p>";
			}
	?>
		</fieldset>
	</div>

    <div class="span6">
        <form id="book" method='post' action='/book'>
				<fieldset>
					<legend>New Book</legend>
					<label class="control-label" for="title">Title</label>
					<input type="text" class="input-xlarge" id="title" style="width:100%" name="title" rel="popover" data-original-title="Title" placeholder="Book Title" required>
					<label class="control-label" for="description">Description</label>
					<textarea name="description" id="description" rows="12" style="width:100%" required></textarea>
					<div class="form-actions">
						<button type="submit" class="btn pull-right" rel="tooltip" title="Create Book">Create New Book <i class="icon-chevron-right icon-white"></i></button>
					</div>
					<div id="reason"></div>
				</fieldset>
        </form>
    </div>
</div>
<script>
$("#book").ajaxForm({
	dataType: 'json',
	success: function(data) {
		if (data.error) {
			$("#reason").html("<div class='alert alert-error'><button type='button' class='close' data-dismiss='alert'>&times;</button>" + data.error + "</div>");
		}
		if (data.ok) {
			location.reload();
		}
	}
});
</script>