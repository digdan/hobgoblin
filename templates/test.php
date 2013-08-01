<FORM METHOD="POST" action="/">
<TABLE>
<TR>
	<TD>Path : </TD>
	<TD><INPUT TYPE="TEXT" name="path" value="<?=$path;?>"></TD>
</TR>
<?php
	for($i=1;$i<=10;$i++) {
		echo "<TR><TD>Var #{$i}</TD><TD><INPUT TYPE=\"text\" name=\"varNames[{$i}]\" value=\"{$varNames[$i]}\"></TD><TD><INPUT TYPE=\"text\" name=\"varValues[{$i}]\" value=\"{$varValues[$i]}\"></TD></TR>";
	}
?>
<TR>
	<TD colspan="2"><INPUT TYPE="submit" name="cmd" value="Submit"></TD>
</TR>
</TABLE>
</FORM>
<HR>
<?= $returned; ?>