%%custom_form_todolist%%
<!--<script src="//%%domain%%/public/js/ajax.js"></script>!-->
<script>
$(function() {

    ajax({"page": "1"}, "get-todoList",  
    function(json) {
        loadPage();
        todoList(json);
    },
    function() {
        loadPage();
        $("#todo-table").text();
        $("#todo-paginator").text("");
        $("#todo-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
    });

    ajax({}, "get-TodoUsers",  
    function(json) {
        $(".loading-nicks").css("visibility", "");
        $(".loading-nicks").removeClass("loading-nicks");
        $("#loader-nicks").remove();

        if (json.success) {
            $("#todo-nicks").html(json.message);
        } else {
            $("#todo-nicks").html("<h2 style=\"color:red;text-align:center;\">" + json.message + "</h2>");
        }
    },
    function() {
        $(".loading-users").css("visibility", "");
        $(".loading-users").removeClass("loading-users");
        $("#loader-users").remove();

        $("#todo-table").text();
        $("#todo-paginator").text("");
        $("#todo-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
    });

    function loadPage()
    {
        $(".loading").css("visibility", "");
        $(".loading").removeClass("loading");
        $("#loader").remove();
    }

    function todoList(json)
    {
        if (json.success) {
            $("#todo-items").html(json.message);
            $("#todo-page-id").text(json.currentpage);
            $("#todo-page-id").attr("data-page", json.currentpage);
            if (json.prev == "enabled") {
                $("#li-todo-prev-page").removeClass("disabled");
            } else {
                $("#li-todo-prev-page").addClass("disabled");
            }
            if (json.next == "enabled") {
                $("#li-todo-next-page").removeClass("disabled");
            } else {
                $("#li-todo-next-page").addClass("disabled");
            }
        } else {
            $("#todo-table").html("<h2 style=\"color:red;text-align:center;\">" + json.message + "</h2>");
            $("#todo-paginator").text("");
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

    /*$("#todo-nick").keyup(function() {
        if ($("#todo-nick").val() != "") {
            ajax({"findningnick": $("#todo-nick").val()}, "get-user-list",
            function(json) {
                if (json.success) {
                    $("#nicks").html(json.message);
                    $("#todo-nick").removeClass("is-invalid");
                    $("#todo-button").prop('disabled', false);
                } else {
                    $("#nicks").html("");
                    $("#todo-nick").addClass("is-invalid");
                    $("#todo-button").prop('disabled', true);
                }
            },
            function (){
                $("#nicks").text("Nelze kontaktovat API.");
            });
        } else {
            $("#nicks").text("");
        }
    });*/

    $("#todo-next-page").on("click", function () {
        var nextpage = (parseInt($("#todo-page-id").attr("data-page"), 10) + 1);
        ajax({"page": nextpage}, "get-todoList",  
        function(json) {
            todoList(json);
        }, 
        function() {
            $("#todo-table").text();
            $("#todo-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
            $("#todo-paginator").text("");
        });
    });

    $("#todo-prev-page").on("click", function () {
        var prevpage = (parseInt($("#todo-page-id").attr("data-page"), 10) - 1);
        ajax({"page": String(prevpage)}, "get-todoList",  
        function(json) {
            todoList(json);
        }, 
        function() {
            $("#todo-table").text();
            $("#todo-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
            $("#todo-paginator").text("");
        });
    });

    /*$(document).on("click", "#snick", function() {
        $("#todo-nick").val($(this).attr("data-nick"));
        $("#nicks").text("");
    });*/
});

</script>