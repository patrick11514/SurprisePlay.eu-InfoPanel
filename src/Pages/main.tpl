<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>%%page%% | %%title_domain%%</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="//%%domain%%/public/imgs/favicon.ico">
    <link rel="stylesheet" href="//%%domain%%/public/css/fontawesome.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link rel="stylesheet" href="//%%domain%%/public/css/style.css">
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro&display=swap" rel="stylesheet">
    <script src="//%%domain%%/public/js/jquery.min.js"></script>
</head>
<body>
    <div class="wrapper">
        <nav id="sidebar">
            <div class="sidebar-header">
                <img src="//%%domain%%/public/imgs/nav_icon.png" class="logo"> 
                <p>SuprisePlay.eu</p>
            </div>

            <ul class="list-unstyled">
                <div class="user">
                    <img src="%%skin_URL%%" class="avatar">
                    <p>%%username%%</p>
                    <p class="rank" style="color:%%RANK_COLOR%%;font-size:1rem;text-shadow: 0 1px 10px rgba(0,0,0,.6);">%%rank%%</p>
                </div>
                %%NAVIGATION%%
            </ul>
        </nav>

        %%content%%

    </div>
<script src="//%%domain%%/public/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('#sidebarCollapse').on('click', function () {
            $('#sidebar').toggleClass('active');
        });
    });
</script>
</body>
</html>
