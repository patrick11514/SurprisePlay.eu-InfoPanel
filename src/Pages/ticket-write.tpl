%%ticket_callback_redirect%%
<div id="content">
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <ul class="nav navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" id="sidebarCollapse" href="#"><i class="fas fa-align-left"></i></a>
                </li>
            </ul>
            <p>%%page_name%%</p>
            <ul class="nav navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="?logout"><i class="fas fa-sign-in-alt"></i></a>
                </li>
            </ul>
        </div>
    </nav>

    <div id="container" class="container-fluid">
        %%ERRORS%%
        %%messages%%
        <div class="card">
            <div class="card-body">
                <p>Založit nový Tiket</p>
                <hr>
                <form method="post" action="./requests.php" role="form">
                    <input type="hidden" name="method" value="ticket-write" required>
                    <input type="hidden" name="source_page" value="?ticket-write" required>
                    <input type="hidden" name="CSRF_token" value="%%CSRF_Token%%" required>
                    <div class="form-group">
                        <label for="name">Název tiketu</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Typ tiketu</label>
                        <select name="type" class="form-control" required>
                            %%ticket_ticket_types%%
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="message">Zpráva</label>
                        <textarea type="text" class="form-control" id="message" name="message" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-light">Vytvořit tiket</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
$(function () {
    if (window.attachEvent) {
        observe = function (element, event, handler) {
            element.attachEvent('on'+event, handler);
        };
    }
    else {
        observe = function (element, event, handler) {
            element.addEventListener(event, handler, false);
        };
    }

    init();
    function init () {
        var text = document.getElementById('message');
        function resize () {
            text.style.height = 'auto';
            text.style.height = text.scrollHeight+'px';
        }
        function delayedResize () {
            window.setTimeout(resize, 0);
        }
        observe(text, 'change',  resize);
        observe(text, 'cut',     delayedResize);
        observe(text, 'paste',   delayedResize);
        observe(text, 'drop',    delayedResize);
        observe(text, 'keydown', delayedResize);
        resize();
    }
});
</script>