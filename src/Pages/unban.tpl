%%custom_form_unban%%
<script>
$(function() {

    function ajax(data, method, success, error)
    {
        data.CSRF_TOKEN = $("#CSRF_TOKEN").val();
        data.method = method;
        $.ajax({
            contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
            url: 'API.php',
            type: 'POST',
            data: data,
            success: success,
            error: error,
        })
    }

    $("#nick").keyup(function() {
        if ($("#nick").val() != "") {
            ajax({"findningnick": $("#nick").val()}, "get-user-list",
            function(json) {
                if (json.success) {
                    $("#nicks").html(json.message);
                    $("#nick").removeClass("is-invalid");
                } else {
                    $("#nicks").html("");
                    $("#nick").addClass("is-invalid");
                }
            },
            function (){
                $("#nicks").text("Nelze kontaktovat API.");
            });
        } else {
            $("#nicks").text("");
        }
    });

    $(document).on("click", "#snick", function() {
        $("#nick").val($(this).attr("data-nick"));
        $("#nicks").text("");
    });
});

</script>