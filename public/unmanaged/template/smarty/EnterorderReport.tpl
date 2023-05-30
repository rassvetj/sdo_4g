<table border='0' cellpadding="4" cellspacing="0">    
	{?foreach name="students" from = $items item = student?}
		<tr>
		    <td>{?$smarty.foreach.students.iteration?}</td>
		    <td>&nbsp;<b>{?$student.LastName?}</b>&nbsp;</td>
            <td>&nbsp;<b>{?$student.FirstName?}</b>&nbsp;</td>
            <td>&nbsp;<b>{?$student.Patronymic?}</b>&nbsp;</td>
		</tr>
	{?/foreach?}    
</table>
<br />
