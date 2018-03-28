function sendRequest(path, params, method) {
    method = method || "post"; // Set method to post by default if not specified.

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);

    for(var key in params) {
        if(params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);

            form.appendChild(hiddenField);
         }
    }

    document.body.appendChild(form);
    form.submit();
}

function updateAccount(requireComment, status, url) {
    var comment = ''
    var promptField = 'Nhập admin comment' + (requireComment ? ' (*)' : '') + ':'

    comment = prompt(promptField)

    if (requireComment) {
        if (comment !== null && !comment) {
            alert('Admin phải nhập comment cho thao tác này.')
            updateAccount(requireComment, status, url)
            return false
        } else if (comment === null) {
            return false
        }
    }

    // Now we sure have comment if requireComment == true
    var statusName = ['yêu cầu cập nhật thêm', 'từ chối', 'duyệt']
    var data = {
        status: status,
        form_key: window.FORM_KEY
    }

    if (requireComment || (!requireComment && comment)) {
        data.comment = comment
    }

    if (confirm('Admin có xác nhận ' + statusName[status - 1] + ' tài khoản này?')) {
        sendRequest(url, data)
    }
}
