%%custom_form_Unregister%%

<script>
$(function() {

    ajax({"page": "1"}, "get-Unregistred-list",  
    function(json) {
        loadPage();
        logList(json);
    },
    function() {
        loadPage();
        $("#allow-unregister-table").text();
        $("#unregister-page-buttons").text("");
        $("#allow-unregister-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
    });

    function loadPage()
    {
        $(".loading").css("visibility", "");
        $(".loading").removeClass("loading");
        $("#loader").remove();
    }

    function logList(json)
    {
        loadPage();
        if (json.success) {
            $("#unregister-allow-user-list").html(json.message);
            $("#unregister-page-id").text(json.currentpage);
            $("#unregister-page-id").attr("data-page", json.currentpage);
            if (json.prev == "enabled") {
                $("#li-unregister-prev-page").removeClass("disabled");
            } else {
                $("#li-unregister-prev-page").addClass("disabled");
            }
            if (json.next == "enabled") {
                $("#li-unregister-next-page").removeClass("disabled");
            } else {
                $("#li-unregister-next-page").addClass("disabled");
            }
        } else {
            $("#allow-unregister-table").html("<h2 style=\"color:red;text-align:center;\">" + json.message + "</h2>");
            $("#unregister-page-buttons").text("");
        }
    }

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

    $("#unregister-nick").keyup(function() {
        if ($("#unregister-nick").val() != "") {
            ajax({"findningnick": $("#unregister-nick").val()}, "get-user-list",
            function(json) {
                if (json.success) {
                    $("#nicks").html(json.message);
                    $("#unregister-nick").removeClass("is-invalid");
                    $("#unregister-button").prop('disabled', false);
                } else {
                    $("#nicks").html("");
                    $("#unregister-nick").addClass("is-invalid");
                    $("#unregister-button").prop('disabled', true);
                }
            },
            function (){
                $("#nicks").text("Nelze kontaktovat API.");
            });
        } else {
            $("#nicks").text("");
        }
    });

    $("#unregister-next-page").on("click", function () {
        var nextpage = (parseInt($("#unregister-page-id").attr("data-page"), 10) + 1);
        ajax({"page": nextpage}, "get-Unregistred-list",  
        function(json) {
            logList(json);
        }, 
        function() {
            $("#allow-unregister-table").text();
            $("#unregister-page-buttons").text("");
            $("#allow-unregister-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
        });
    });

    $("#unregister-prev-page").on("click", function () {
        var prevpage = (parseInt($("#unregister-page-id").attr("data-page"), 10) - 1);
        ajax({"page": String(prevpage)}, "get-Unregistred-list",  
        function(json) {
            logList(json);
        }, 
        function() {
            $("#allow-unregister-table").text();
            $("#unregister-page-buttons").text("");
            $("#allow-unregister-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
        });
    });

    $(document).on("click", "#snick", function() {
        $("#unregister-nick").val($(this).attr("data-nick"));
        $("#nicks").text("");
    });
});

</script>