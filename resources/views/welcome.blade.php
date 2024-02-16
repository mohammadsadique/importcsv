<!DOCTYPE html>
<html lang="en">
<head>
  <title>iCloudEMS Assignment</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.js"></script>
  <link rel="stylesheet" href="{{url('/')}}/public/custom.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <div class="row mt-5">
        <div class="col-md-12">
            <span class="error"></span>
            <h4>iCloudEMS</h4>
            <form  id="itemForm" enctype="multipart/form-data" >
                @csrf
                <div class="box">
                    <div class="form-group">
                        <label for="import">Excel File:</label>
                        <div class="custom-file">
                            <input type="file" id="file" name="file" class="custom-file-input" id="import" accept=".xlsx, .xls , .csv">
                            <label class="custom-file-label" for="import">Choose file</label>
                        </div>
                    </div>
                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-primary font-weight-bold">Import</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="loaderContainer" style="display: none;">
    <div class="loader">
        <div class="box">
            <img src="{{url('/')}}/public/loader.gif" alt="Loader">
        </div>
    </div>
</div>



</body>
<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    })
    var fileInput = null;
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        fileInput = this; 
    });
     $(document).on('submit','#itemForm',function(e){
        e.preventDefault();   
        var formData = new FormData(this);   
        var loaderContainer = $('#loaderContainer');
        loaderContainer.show();
        var form = this; 
        $.ajax({
            url: "{{ route('excelImport') }}",
            type: 'POST',
            data: formData,
            datatype: "json",
            processData: false,
            contentType: false,
            success: function(res){
                console.log('res = ' , res);
                // Hide the loader after the request is complete
                $('.error').empty().html(
                `<div class="alert alert-success">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <strong>Success!</strong> File imported successfully.
                </div>
                `);
                loaderContainer.hide();
                form.reset();
                $(".custom-file-label").removeClass("selected").html('Choose file');
               
            }, error: function(xhr, status, error) {
                // Handle error response
                $('.error').empty().html(
                `
                <div class="alert alert-danger">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                    <strong>Error!</strong> Error occurred while importing the file.
                </div>
                `);
                loaderContainer.hide();

            }
        });
    });







</script>
</html>
