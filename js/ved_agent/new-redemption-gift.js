$j = jQuery.noConflict()

$j(document).ready(function() {
    $j('.entry-edit').hide()
    $j('.content-header').hide()
    $j('.content-header:first').show()
})

function selectSalesRule(grid, event) {
    var element = Event.findElement(event, 'tr')
    var rowId = $j(element).find('td:first').text().trim()
    var rowName = $j(element).find('td:nth-child(2)').text().trim()
    console.log(rowId);
    console.log(rowName);

    $j('label[for="rule_id_show"]:first').parent().next().text(rowId);
    $j('label[for="rule_name_show"]:first').parent().next().text(rowName);

    $j('#rule_id').val(rowId);

    $j('.entry-edit').show()
    $j('.content-header').show()
    $j('.content-header:first').hide()
    $j('[class^=grid-container-]').remove()
}
