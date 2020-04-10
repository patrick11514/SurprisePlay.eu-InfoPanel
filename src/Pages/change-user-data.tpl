%%custom_form_change_user%%
<script>
$(function() {

    ajax({"page": "1"}, "get-transfer-list",  
    function(json) {
        loadPage();
        transferList(json);
    },
    function() {
        loadPage();
        $("#transfer-table").text();
        $("#transfer-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
        $("#transfer-page-buttons").text("");
    });

    function loadPage()
    {
        $(".loading").css("visibility", "");
        $(".loading").removeClass("loading");
        $("#loader").remove();
    }

    function transferList(json)
    {
        if (json.success) {
            $("#transfer-user-list").html(json.message);
            $("#transfer-page-id").text(json.currentpage);
            $("#transfer-page-id").attr("data-page", json.currentpage);
            if (json.prev == "enabled") {
                $("#li-transfer-prev-page").removeClass("disabled");
            } else {
                $("#li-transfer-prev-page").addClass("disabled");
            }
            if (json.next == "enabled") {
                $("#li-transfer-next-page").removeClass("disabled");
            } else {
                $("#li-transfer-next-page").addClass("disabled");
            }
        } else {
            $("#transfer-table").html("<h2 style=\"color:red;text-align:center;\">" + json.message + "</h2>");
            $("#transfer-page-buttons").text("");
        }
    }

    $("#transfer-next-page").on("click", function () {
        var nextpage = (parseInt($("#transfer-page-id").attr("data-page"), 10) + 1);
        ajax({"page": nextpage}, "get-transfer-list",  
        function(json) {
            transferList(json);
        }, 
        function() {
            $("#transfer-table").text();
            $("#transfer-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
            $("#transfer-page-buttons").text("");
        });
    });

    $("#transfer-prev-page").on("click", function () {
        var prevpage = (parseInt($("#transfer-page-id").attr("data-page"), 10) - 1);
        ajax({"page": String(prevpage)}, "get-transfer-list",  
        function(json) {
            transferList(json);
        }, 
        function() {
            $("#transfer-table").text();
            $("#transfer-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
            $("#transfer-page-buttons").text("");
        });
    });

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