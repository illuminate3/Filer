<div class="dropzone" id="{!!$field!!}"></div>

<script type="text/javascript">
    var drop = $("div#{!!$field!!}").dropzone({
        url: "/{!! $path !!}",
        maxFiles: {!!$files!!},
        acceptedFiles: "image/*",
        maxfilesexceeded: function(file) {
            toastr.error('Files exceedes maximum size.', 'Error');
        },
        sending: function(file, xhr, formData) {
            // Pass token. You can use the same method to pass any other values as well such as a id to associate the image with for example.
            formData.append("_token", $('[name=_token]').val()); // Laravel expect the token post value to be named _token by default
        },
        init: function() {
            this.on("success", function(file, response) {
                toastr.success('Files uploaded successfully.', 'Success');
            });
        }
        
    });
</script>