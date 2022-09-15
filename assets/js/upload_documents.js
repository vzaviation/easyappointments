function uploadDocument(id) {

	var files = $('#'+id)[0].files;

	var url = GlobalVariables.baseUrl + '/index.php/appointments/ajax_upload_document';

	var form = new FormData();
    form.append('user_document', files[0]);
    form.append('csrfToken', GlobalVariables.csrfToken);

    $.ajax({
      type:'POST',
      url: url,
      data: form,
      contentType: false,
      processData: false,
      beforeSend: function() {
	$(':input[type="button"]').prop('disabled', true);
        console.log("uploading document...");
      },
      success: (response) => {
      	console.log(response);
      	if(response.error){
      		alert("Error while uploading the file, please try again");
      	} else{
      		var fileName = response.fileName;
      		$('#'+id+'-file-name').val(fileName);
	}
	$(':input[type="button"]').prop('disabled', false);
      },
      error: function(response){
	$(':input[type="button"]').prop('disabled', false);
      	console.log(response);
      }
  });

}
