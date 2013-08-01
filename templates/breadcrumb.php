<?php
	$breadcrumbs = HG::v("breadcrumbs");
	if (is_array($breadcrumbs)) {
		echo "<ul class=\"breadcrumb\">";
		echo "<li><a href=\"/\">Home</a>";

		if (count($breadcrumbs) > 0) { //Had listings
			echo " <span class=\"divider\">/</span>";
		}

		echo "</li>\n";

		end($breadcrumbs);
		$lastkey = key($breadcrumbs);
		foreach($breadcrumbs as $k=>$v) {
			if ($k == $lastkey) {
				if ($v === TRUE) {
					echo "<li CLASS=\"active\">{$k}</li>\n";
				} else {
					echo "<li CLASS=\"active\"><A HREF=\"{$v}\">{$k}</A></li>\n";
				}
			} else {
				if ($v === TRUE) {
					echo "<li>{$k} <span class=\"divider\">/</span></li>\n";
				} else {
					echo "<li><A HREF=\"{$v}\">{$k}</A><span class=\"divider\">/</span></li>\n";
				}
			}
		}
		echo "</ul>";
	}
?>
