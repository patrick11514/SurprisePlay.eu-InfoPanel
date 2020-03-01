%%custom_form_VPN%%

<script>
$(function() {

    ajax({"page": "1"}, "get-allowVPN-list",  
    function(json) {
        vpnList(json);
    },
    function() {
        $("#allow-vpn-table").text();
        $("#allow-vpn-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
    });

    function vpnList(json)
    {
        if (json.success) {
            $("#vpn-allow-user-list").html(json.message);
            $("#vpn-page-id").text(json.currentpage);
            $("#vpn-page-id").attr("data-page", json.currentpage);
            if (json.prev == "enabled") {
                $("#li-vpn-prev-page").removeClass("disabled");
            } else {
                $("#li-vpn-prev-page").addClass("disabled");
            }
            if (json.next == "enabled") {
                $("#li-vpn-next-page").removeClass("disabled");
            } else {
                $("#li-vpn-next-page").addClass("disabled");
            }
        } else {
            $("#allow-vpn-table").html("<h2 style=\"color:red;text-align:center;\">" + json.message + "</h2>");
            $("#vpn-page-buttons").text("");
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
                    $("#vpn-button").prop('disabled', false);
                } else {
                    $("#nicks").html("");
                    $("#allow-nick").addClass("is-invalid");
                    $("#vpn-button").prop('disabled', true);
                }
            },
            function (){
                $("#nicks").text("Nelze kontaktovat API.");
            });
        } else {
            $("#nicks").text("");
        }
    });

    $("#vpn-next-page").on("click", function () {
        var nextpage = (parseInt($("#vpn-page-id").attr("data-page"), 10) + 1);
        ajax({"page": nextpage}, "get-allowVPN-list",  
        function(json) {
            vpnList(json);
        }, 
        function() {
            $("#allow-vpn-table").text();
            $("#allow-vpn-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
        });
    });

    $("#vpn-prev-page").on("click", function () {
        var prevpage = (parseInt($("#vpn-page-id").attr("data-page"), 10) - 1);
        ajax({"page": String(prevpage)}, "get-allowVPN-list",  
        function(json) {
            vpnList(json);
        }, 
        function() {
            $("#allow-vpn-table").text();
            $("#allow-vpn-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
        });
    });

    $(document).on("click", "#snick", function() {
        $("#allow-nick").val($(this).attr("data-nick"));
        $("#nicks").text("");
    });
});

</script>