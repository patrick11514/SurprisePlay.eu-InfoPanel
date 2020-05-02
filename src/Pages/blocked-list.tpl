%%custom_form_BLOCKED%%

<script>
$(function() {

    ajax({"page": "1"}, "get-blockedList",  
    function(json) {
        loadPage();
        userList(json);
    },
    function() {
        loadPage();
        $("#blocked-user-table").text();
        $("#blocked-user-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
        $("#blocked-user-page-buttons").text("");
    });

    function loadPage()
    {
        $(".loading").css("visibility", "");
        $(".loading").removeClass("loading");
        $("#loader").remove();
    }

    function userList(json)
    {
        if (json.success) {
            $("#blocked-user-list").html(json.message);
            $("#blocked-user-page-id").text(json.currentpage);
            $("#blocked-user-page-id").attr("data-page", json.currentpage);
            if (json.prev == "enabled") {
                $("#li-blocked-user-prev-page").removeClass("disabled");
            } else {
                $("#li-blocked-user-prev-page").addClass("disabled");
            }
            if (json.next == "enabled") {
                $("#li-blocked-user-next-page").removeClass("disabled");
            } else {
                $("#li-blocked-user-next-page").addClass("disabled");
            }
        } else {
            $("#blocked-user-table").html("<h2 style=\"color:red;text-align:center;\">" + json.message + "</h2>");
            $("#blocked-user-page-buttons").text("");
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

    $("#allow-nick").keyup(function() {
        if ($("#allow-nick").val() != "") {
            ajax({"findningnick": $("#allow-nick").val()}, "get-user-list",
            function(json) {
                if (json.success) {
                    $("#nicks").html(json.message);
                    $("#allow-nick").removeClass("is-invalid");
                    $("#blocked-user-button").prop('disabled', false);
                } else {
                    $("#nicks").html("");
                    $("#allow-nick").addClass("is-invalid");
                    $("#blocked-user-button").prop('disabled', true);
                }
            },
            function (){
                $("#nicks").text("Nelze kontaktovat API.");
            });
        } else {
            $("#allow-nick").removeClass("is-invalid");
            $("#blocked-user-button").prop('disabled', false);
            $("#nicks").text("");
        }
    });

    $("#blocked-user-next-page").on("click", function () {
        var nextpage = (parseInt($("#blocked-user-page-id").attr("data-page"), 10) + 1);
        ajax({"page": nextpage}, "get-blockedList",  
        function(json) {
            userList(json);
        }, 
        function() {
            $("#blocked-user-table").text();
            $("#blocked-user-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
            $("#blocked-user-page-buttons").text("");
        });
    });

    $("#blocked-user-prev-page").on("click", function () {
        var prevpage = (parseInt($("#blocked-user-page-id").attr("data-page"), 10) - 1);
        ajax({"page": String(prevpage)}, "get-blockedList",  
        function(json) {
            userList(json);
        }, 
        function() {
            $("#blocked-user-table").text();
            $("#blocked-user-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
            $("#blocked-user-page-buttons").text("");
        });
    });

    $(document).on("click", "#snick", function() {
        $("#allow-nick").val($(this).attr("data-nick"));
        $("#nicks").text("");
    });
});

</script>