var $j = jQuery.noConflict()
var userClickedOpenMessenger = false

var showMessengerPlugins = function() {
    userClickedOpenMessenger = true
    $j('.fb-customerchat').show()
    $j('.floating-bar-menu-item.facebook-message').css('pointer-events', 'none')
}

$j(document).ready(function() {
    $j('.fb-customerchat').hide()

    $j('.floating-bar-menu-item.facebook-message').on('click', function() {
        if (!userClickedOpenMessenger) {
            showMessengerPlugins()
        }
    })

    setTimeout(function() {
        showMessengerPlugins()
    }, 30000)
})
