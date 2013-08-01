<H1>Story Idea Generator</H1>
<I>( sourced from tvtropes.com )</I>
<BR>

<?
	foreach($tropes as $type=>$trope) {
		echo "<H2>{$type}</H2>";
		echo "<div class='well trope'>";

			echo "<H3>{$trope["name"]}</H3>";
			echo "{$trope["desc"]}";

		echo "</div>";
		echo "</div>";
		echo "<div class='break'><BR></div>";
	}
?>