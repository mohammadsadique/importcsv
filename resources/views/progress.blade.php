<!DOCTYPE html>
<html>
<head>
    <title>Progress Bar</title>
    <!-- Include your CSS here -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.4/dist/jquery.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="container mt-3">
    <h2>Import Data Progress</h2>
    <div class="progress">
        <div class="progress-bar progress-bar-striped" style="width:70%">70%</div>
    </div>
    <div>
        <b>Start Time :-</b> <span class="createdAt"></span> <br>
        <b>End Time &nbsp; :-</b> <span class="finishedAt"></span>
    </div>
</div>

    @if (session('id'))
        <input type="hidden" value="{{ session('id') }}">
    @endif

    <div id="progress-container">
        <div id="progress-bar"></div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            // Function to update the progress bar
            function updateProgressBar(progress , finishedAt) {
                $('.progress-bar').html(progress+'%')
                $('.progress-bar').css('width', progress + '%');

                if (progress === 100) {
                    console.log('finishedAt = ', finishedAt)
                    $('.finishedAt').html(finishedAt);
                    return;
                }

                // Set up a timeout to periodically fetch progress
                setTimeout(fetchProgress, 1000);
            }

            // AJAX request to fetch progress
            function fetchProgress() {
                let id = $('input').val();
                $.ajax({
                    url: "{{ route('batch', '') }}" + "/"+id,
                    type: 'GET',
                    datatype: "json",
                    success: function (data) {
                        // console.log(data)
                        updateProgressBar(data.progress , data.finishedAt);
                        $('.createdAt').html(data.createdAt);
                    }
                });
            }

            // Start fetching progress
            fetchProgress();
        });
    </script>
</body>
</html>
