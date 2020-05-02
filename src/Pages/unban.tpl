%%custom_form_unban%%
<script>
$(function() {

    ajax({"page": "1"}, "get-unban-list",  
    function(json) {
        loadPage();
        unbanList(json);
    },
    function() {
        loadPage();
        $("#unban-table").text();
        $("#unban-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
        $("#unban-page-buttons").text("");
    });

    function loadPage()
    {
        $(".loading").css("visibility", "");
        $(".loading").removeClass("loading");
        $("#loader").remove();
    }

    function unbanList(json)
    {
        if (json.success) {
            $("#unban-user-list").html(json.message);
            $("#unban-page-id").text(json.currentpage);
            $("#unban-page-id").attr("data-page", json.currentpage);
            if (json.prev == "enabled") {
                $("#li-unban-prev-page").removeClass("disabled");
            } else {
                $("#li-unban-prev-page").addClass("disabled");
            }
            if (json.next == "enabled") {
                $("#li-unban-next-page").removeClass("disabled");
            } else {
                $("#li-unban-next-page").addClass("disabled");
            }
        } else {
            $("#unban-table").html("<h2 style=\"color:red;text-align:center;\">" + json.message + "</h2>");
            $("#unban-page-buttons").text("");
        }
    }

     $("#unban-next-page").on("click", function () {
        var nextpage = (parseInt($("#unban-page-id").attr("data-page"), 10) + 1);
        ajax({"page": nextpage}, "get-unban-list",  
        function(json) {
            unbanList(json);
        }, 
        function() {
            $("#unban-table").text();
            $("#unban-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
            $("#unban-page-buttons").text("");
        });
    });

    $("#unban-prev-page").on("click", function () {
        var prevpage = (parseInt($("#unban-page-id").attr("data-page"), 10) - 1);
        ajax({"page": String(prevpage)}, "get-unban-list",  
        function(json) {
            unbanList(json);
        }, 
        function() {
            $("#unban-table").text();
            $("#unban-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
            $("#unban-page-buttons").text("");
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
            ("#nick").addClass("is-invalid");
            $("#nicks").text("");
        }
    });

    $(document).on("click", "#snick", function() {
        $("#nick").val($(this).attr("data-nick"));
        $("#nicks").text("");
    });
});

</script>