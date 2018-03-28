$j(document).ready(function () {
    $j('input[name="prepaid_amount_mask"]').mask("#.##0", {reverse: true});
    $j('input[name="fee_mask"]').mask("#.##0", {reverse: true});
});
function submitEditForm() {
    $j('input[name="fee"]').val($j('input[name="fee_mask"]').cleanVal());
    $j('input[name="prepaid_amount"]').val($j('input[name="prepaid_amount_mask"]').cleanVal());
    editForm = new varienForm('edit_form', '');
    editForm.submit();
}