%%custom_form_gems%%

<script>
$(function() {

    ajax({"page": "1"}, "get-gemsLog",  
    function(json) {
        loadPage();
        gemsList(json);
    },
    function() {
        loadPage();
        $("#allow-gems-table").text();
        $("#gems-page-buttons").text("");
        $("#allow-gems-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
    });

    button_text = "Přidat {X} gemů.";

    var gem_count = 0;

    function loadPage()
    {
        $(".loading").css("visibility", "");
        $(".loading").removeClass("loading");
        $("#loader").remove();
    }

    $("#gem-action").change(function() {
        if ($("#gem-action").val() == "add") {
            button_text = "Přidat {X} gemů.";
        } else {
            button_text = "Odebrat {X} gemů.";
        }
        var count = $("#gems-button").attr("gem-amount");
        set_button(parseInt(count));
    });

    set_button(gem_count);

    function set_button(count)
    {
        console.log(button_text);
        $("#gems-button").text(button_text.replace("{X}", count.toString()));
        $("#gems-button").attr("gem-amount", count.toString());
    }

    $("#gem-count").keyup(function() {
        if ($("#gem-count").val() != "") {
            var gems = parseInt($("#gem-count").val());
            
        } else {
            var gems = 0;
        }
        set_button(gems);      
    });

    var nick = false;
    var count = false;

    //Povolení Buttonu
    $("#gem-count").keyup(function() {
        if ($("#gem-count").val() != "") {
            count = true;
        } else {
            count = false;
        }
        checkButton();
    });

    $("#gems-nick").keyup(function() {
        if ($("#gems-nick").val() != "") {
            nick = true;
        } else {
            nick = false;
        }
        checkButton();
    });

    function checkButton()
    {
        if (nick === true && count === true) {
            $("#gems-button").removeClass("disabled");
        } else {
            $("#gems-button").addClass("disabled");
        }
    }

    function gemsList(json)
    {
        if (json.success) {
            $("#gems-allow-user-list").html(json.message);
            $("#gems-page-id").text(json.currentpage);
            $("#gems-page-id").attr("data-page", json.currentpage);
            if (json.prev == "enabled") {
                $("#li-gems-prev-page").removeClass("disabled");
            } else {
                $("#li-gems-prev-page").addClass("disabled");
            }
            if (json.next == "enabled") {
                $("#li-gems-next-page").removeClass("disabled");
            } else {
                $("#li-gems-next-page").addClass("disabled");
            }
        } else {
            $("#allow-gems-table").html("<h2 style=\"color:red;text-align:center;\">" + json.message + "</h2>");
            $("#gems-page-buttons").text("");
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

    $("#gems-nick").keyup(function() {
        if ($("#gems-nick").val() != "") {
            ajax({"findningnick": $("#gems-nick").val()}, "get-user-list",
            function(json) {
                if (json.success) {
                    $("#nicks").html(json.message);
                    $("#gems-nick").removeClass("is-invalid");
                    $("#gems-button").prop('disabled', false);
                } else {
                    $("#nicks").html("");
                    $("#gems-nick").addClass("is-invalid");
                    $("#gems-button").prop('disabled', true);
                }
            },
            function (){
                $("#nicks").text("Nelze kontaktovat API.");
            });
        } else {
            $("#nicks").text("");
        }
    });

    $("#gems-next-page").on("click", function () {
        var nextpage = (parseInt($("#gems-page-id").attr("data-page"), 10) + 1);
        ajax({"page": nextpage}, "get-gemsLog",  
        function(json) {
            gemsList(json);
        }, 
        function() {
            $("#allow-gems-table").text();
            $("#allow-gems-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
            $("#gems-page-buttons").text("");
        });
    });

    $("#gems-prev-page").on("click", function () {
        var prevpage = (parseInt($("#gems-page-id").attr("data-page"), 10) - 1);
        ajax({"page": String(prevpage)}, "get-gemsLog",  
        function(json) {
            gemsList(json);
        }, 
        function() {
            $("#allow-gems-table").text();
            $("#allow-gems-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
            $("#gems-page-buttons").text("");
        });
    });

    $(document).on("click", "#snick", function() {
        $("#gems-nick").val($(this).attr("data-nick"));
        $("#nicks").text("");
    });
});

</script>