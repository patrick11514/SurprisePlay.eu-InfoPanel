%%custom_form_change_user%%
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

    $("#from-nick").keyup(function() {
        if ($("#from-nick").val() != "") {
            ajax({"findningnick": $("#from-nick").val()}, "get-user-list",
            function(json) {
                if (json.success) {
                    $("#from-nicks").html(json.message);
                    $("#from-nick").removeClass("is-invalid");
                } else {
                    $("#from-nicks").html("");
                    $("#from-nick").addClass("is-invalid");
                }
            },
            function (){
                $("#from-nicks").text("Nelze kontaktovat API.");
            });
        } else {
            $("#from-nicks").text("");
        }
    });

    $("#to-nick").keyup(function() {
        if ($("#to-nick").val() != "") {
            ajax({"findningnick": $("#to-nick").val()}, "get-user-list",
            function(json) {
                if (json.success) {
                    $("#to-nicks").html(json.message);
                    $("#to-nick").removeClass("is-invalid");
                } else {
                    $("#to-nicks").html("");
                    $("#to-nick").addClass("is-invalid");
                }
            },
            function (){
                $("#to-nicks").text("Nelze kontaktovat API.");
            });
        } else {
            $("#to-nicks").text("");
        }
    });

    $(document).on("click", "#snick", function() {
        if ($(this).parent().attr("pos") == "1") {
            $("#from-nick").val($(this).attr("data-nick"));
            $("#from-nicks").text("");
        } else {
            $("#to-nick").val($(this).attr("data-nick"));
            $("#to-nicks").text("");
        }
    });
});
</script>