function _checkCommentThenSubmit() {
    var commentElement = document.getElementById('comment')

    if (!commentElement.value) {
        if (!commentElement.classList.contains('validation-failed')) {
            commentElement.classList.add('validation-failed')

            var notice = document.createElement('div')
            notice.setAttribute('id', 'validation-comment')
            notice.innerHTML = 'Phải nhập thông tin'
            notice.setAttribute('class', 'validation-advice')


            commentElement.parentNode.insertBefore(notice, commentElement.parentNode.lastChild)
        }
    } else {
        var notice = document.getElementById('validation-comment')
        if (notice) {
            notice.innerHTML = ''
            notice.removeAttribute('class')
        }

        editForm.submit()
    }
}

function verifyAndSave() {
    document.getElementById('status').value = '3'
    editForm.submit()
}

function declineAndSave() {
    document.getElementById('status').value = '2'
    _checkCommentThenSubmit()
}

function requireUpdateAndSave() {
    document.getElementById('status').value = '1'
    _checkCommentThenSubmit()
}

function nothingAndSave() {
    document.getElementById('status').outerHTML = ''
    editForm.submit()
}
