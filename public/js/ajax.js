$(function() {
   
    var type = $("[id*='-paginator'").attr("data-ajax-var");
    var request = $("#" + type + "-table").attr("data-request");
    
    console.log(type);
    console.log(request);

    removeLoading();

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

    //remove-loading
    function removeLoading(){
        $(".loading").css("visibility", "");
        $(".loading").removeClass("loading");
        $("#loader").remove();
    }

    function ajaxList(json)
    {
        console.log(json);
    }

    //next-page
    $("#" + type + "-next-page").on("click", function() {
        /*var prevpage = (parseInt($("#" + type + "-page-id").attr("data-page"), 10) - 1);
        ajax({"page": String(prevpage)}, request,  
        function(json) {
            ajaxList(json);
        }, 
        function() {
            $("#" + type + "-table").text("");
            $("#allow-gems-table").html("<h2 style=\"color:red;text-align:center;\">Nelze kontaktovat API!</h2>");
            $("#" + type + "-paginator").text("");
        });*/
        console.log(parseInt($("#" + type + "-page-id").attr("data-page"), 10) - 1);
    });

});