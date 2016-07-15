(function() {

    // Shim XHR for IE6
    var newRequest;
    if(window.XMLHttpRequest) {
        newRequest = function() {
            return new window.XMLHttpRequest();
        }
    } else {
        newRequest = function() {
            try {
                return new ActiveXObject("Microsoft.XMLHTTP");
            } catch(ex) {
                return new ActiveXObject("MSXML2.XMLHTTP.3.0");
            }
        }
    }

    // Shim JSON for IE6
    var JSON = window.JSON || { parse: eval };

    // Shim cookies API
    var cookies = {};
    cookies.setItem = function(nom, valeur) {
        try {
            var argv = arguments;
            var argc = arguments.length;
            var expires = (argc > 2) ? argv[2] : null;
            var path = (argc > 3) ? argv[3] : null;
            var domain = (argc > 4) ? argv[4] : null;
            var secure = (argc > 5) ? argv[5] : false;

            document.cookie = escape(nom) + "=" + escape(valeur) +
            ((expires == null) ? "" : ("; expires=" + expires.toGMTString())) +
            ((path == null) ? "" : ("; path=" + path)) +
            ((domain == null) ? "" : ("; domain=" + domain)) +
            ((secure == true) ? "; secure" : "");
        } catch(ex) { }
    }
    cookies.getItem = function(nom) {
        try {
            var arg = escape(nom) + "=";
            var alen = arg.length;
            var clen = document.cookie.length;
            var i = 0;
            while(i < clen) {
                var j = i + alen;
                if(document.cookie.substring(i, j) == arg) return cookies.getValueAt(j);
                i = document.cookie.indexOf(" ", i) + 1;
                if(i == 0) break;
            }
        } catch(ex) { }
        return null;
    }
    cookies.getValueAt = function(offset) {
        var endstr = document.cookie.indexOf(";", offset);
        if(endstr == -1) endstr = document.cookie.length;
        return unescape(document.cookie.substring(offset, endstr));
    }


    // Get the comment div
    var commentDiv = document.getElementById("comment-div");
    var commentFormDiv = document.getElementById("comment-form-div");

    // The loadComment function
    function loadComment(commentFile) {

        // Display comment placeholder
        var el = document.createElement("pre");
        el.innerText = el.textContent = "This comment is being loaded...";
        commentDiv.appendChild(el);

        // Get its content
        var xhr = newRequest();
        xhr.onreadystatechange = function() {
            if(this.readyState == 4 && this.status == 200) {

                // Update comment text
                if(el.textContent) {
                    el.textContent = this.responseText.replace(/\r\n/g, "\n");
                } else {
                    el.innerText = this.responseText.replace(/\r\n/g, "\n");
                }

                // Clean variables
                el = null; this.onreadystatechange = null;
            }
        }
        xhr.open("GET", "./comments/" + commentFile, true);
        xhr.send();
    }

    // Load the comments
    var dirName = location.pathname;
    if(dirName.lastIndexOf('/') !== dirName.length - 1) {
        dirName = dirName + '/';
    }
    var xhr = newRequest();
    xhr.onreadystatechange = function() {
        if(this.readyState == 4 && this.status == 200) {

            // Clear comment div content
            commentDiv.innerHTML = "";

            // Initiate comment load
            var comments = JSON.parse(this.responseText);
            for(var i = 0; i < comments.length; i++) {
                loadComment(comments[i]);
            }

            // Clean variables
            this.onreadystatechange = null;
        }
    }
    xhr.open("POST", "../../API/get-comments/?d=" + escape(dirName), true);
    xhr.send("")

    // Add the comment form
    var form = document.createElement("form");
    form.method = "POST";
    form.action = "../../API/post-comment/?d=" + escape(dirName);
    form.onsubmit = function() {
        var expires = (new Date());
        expires.setFullYear(expires.getFullYear() + 1);
        cookies.setItem("author", document.getElementsByName("author")[0].value, expires);
    }

    form.innerHTML = '<label>Author<input type="text" name="author" /></label><label>Message<textarea name="message" rows="10"></textarea></label><input type="submit" value="Publish my message" />';
    commentFormDiv.appendChild(form);

    // Fill the author name automatically, if it's known
    var authorName = cookies.getItem("author");
    if(authorName) document.getElementsByName("author")[0].value = authorName;


})()